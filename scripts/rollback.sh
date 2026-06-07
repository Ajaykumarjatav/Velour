#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════════════════
#  Velour — Rollback script
#  Restores the previous image and re-runs its migration state.
#
#  Usage:
#    ./scripts/rollback.sh
#    ./scripts/rollback.sh --to-backup /var/backups/velour/velour_db_20240101.sql.gz
# ══════════════════════════════════════════════════════════════════════════════
set -euo pipefail

COMPOSE_FILE="docker-compose.prod.yml"
ROLLBACK_IMAGE_FILE="/tmp/velour_previous_image"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/velour}"
DB_BACKUP=""

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

log()     { echo -e "${CYAN}[$(date '+%H:%M:%S')]${RESET} $*"; }
success() { echo -e "${GREEN}✓${RESET} $*"; }
warn()    { echo -e "${YELLOW}⚠${RESET}  $*"; }
die()     { echo -e "${RED}✗ ERROR:${RESET} $*" >&2; exit 1; }

while [[ $# -gt 0 ]]; do
    case "$1" in
        --to-backup) DB_BACKUP="$2"; shift 2;;
        *) die "Unknown argument: $1";;
    esac
done

echo -e "${BOLD}${RED}══════════════════════════════════════${RESET}"
echo -e "${BOLD}${RED}  Velour ROLLBACK — $(date '+%H:%M:%S')${RESET}"
echo -e "${BOLD}${RED}══════════════════════════════════════${RESET}"

# ── Restore previous Docker image ─────────────────────────────────────────────
if [ -f "$ROLLBACK_IMAGE_FILE" ]; then
    PREVIOUS_IMAGE=$(cat "$ROLLBACK_IMAGE_FILE")
    log "Rolling back to image: ${PREVIOUS_IMAGE:0:20}..."
    docker tag "$PREVIOUS_IMAGE" velour/app:rollback
    VELOUR_IMAGE="velour/app:rollback" \
        docker compose -f "$COMPOSE_FILE" up -d --force-recreate app
    success "Image rolled back"
else
    warn "No rollback image reference found. Restarting current containers."
    docker compose -f "$COMPOSE_FILE" restart app
fi

# ── Restore DB from backup if specified ───────────────────────────────────────
if [ -n "$DB_BACKUP" ]; then
    [ -f "$DB_BACKUP" ] || die "Backup file not found: $DB_BACKUP"

    log "WARNING: This will REPLACE the database with: $DB_BACKUP"
    read -r -p "Type 'yes' to confirm: " confirm
    [ "$confirm" = "yes" ] || die "Aborted."

    log "Restoring database ..."
    source .env 2>/dev/null || true

    gunzip -c "$DB_BACKUP" | docker compose -f "$COMPOSE_FILE" exec -T postgres \
        psql -U "${DB_USERNAME:-velour}" "${DB_DATABASE:-velour_saas}" \
        || die "Database restore failed"

    success "Database restored from ${DB_BACKUP}"
else
    log "Running artisan migrate:rollback (last batch) ..."
    docker compose -f "$COMPOSE_FILE" exec -T app \
        php artisan migrate:rollback --force --no-interaction 2>/dev/null || \
        warn "Migration rollback skipped (no previous batch found)"
fi

# ── Clear caches ──────────────────────────────────────────────────────────────
log "Clearing caches ..."
docker compose -f "$COMPOSE_FILE" exec -T app php artisan optimize:clear >/dev/null 2>&1 || true

# ── Restart queue ─────────────────────────────────────────────────────────────
docker compose -f "$COMPOSE_FILE" exec -T app php artisan horizon:terminate >/dev/null 2>&1 || true

docker compose -f "$COMPOSE_FILE" exec nginx nginx -s reload 2>/dev/null || true

echo -e "\n${GREEN}${BOLD}  ✓ Rollback complete${RESET}\n"
echo -e "  Verify at: ${APP_URL:-http://localhost}/up\n"
