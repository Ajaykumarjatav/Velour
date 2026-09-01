#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════════════════
#  Velour — Zero-downtime production deploy script
#
#  Usage:
#    ./scripts/deploy.sh [--tag v1.2.3] [--branch main]
#
#  Prerequisites:
#    - Docker + docker compose installed on server
#    - .env file present (copied from .env.production.example)
#    - SSL certs present in docker/ssl/ or managed via certbot volume
#    - VELOUR_IMAGE set in .env (e.g. ghcr.io/velour/app)
# ══════════════════════════════════════════════════════════════════════════════
set -euo pipefail

# ── Config ────────────────────────────────────────────────────────────────────
COMPOSE_FILE="docker-compose.prod.yml"
APP_SERVICE="app"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/velour}"
SLACK_WEBHOOK="${SLACK_WEBHOOK:-}"
DEPLOY_TAG="${DEPLOY_TAG:-latest}"
HEALTH_URL="${APP_URL:-http://localhost}/up"
ROLLBACK_IMAGE_FILE="/tmp/velour_previous_image"

# ── Colours ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

log()     { echo -e "${CYAN}[$(date '+%H:%M:%S')]${RESET} $*"; }
success() { echo -e "${GREEN}✓${RESET} $*"; }
warn()    { echo -e "${YELLOW}⚠${RESET}  $*"; }
error()   { echo -e "${RED}✗ ERROR:${RESET} $*" >&2; }
die()     { error "$*"; notify_slack "❌ Deploy FAILED: $*"; exit 1; }

# ── Slack notification ────────────────────────────────────────────────────────
notify_slack() {
    [ -z "$SLACK_WEBHOOK" ] && return
    curl -s -X POST "$SLACK_WEBHOOK" \
        -H 'Content-type: application/json' \
        -d "{\"text\": \"[Velour] $1\"}" >/dev/null 2>&1 || true
}

# ── Parse args ────────────────────────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
    case "$1" in
        --tag)    DEPLOY_TAG="$2"; shift 2;;
        --branch) DEPLOY_BRANCH="$2"; shift 2;;
        *)        die "Unknown argument: $1";;
    esac
done

# ── Pre-flight checks ─────────────────────────────────────────────────────────
preflight() {
    log "Running pre-flight checks ..."

    command -v docker  >/dev/null 2>&1 || die "docker not found"
    command -v curl    >/dev/null 2>&1 || die "curl not found"

    [ -f ".env" ]          || die ".env file not found. Copy from .env.production.example"
    [ -f "$COMPOSE_FILE" ] || die "$COMPOSE_FILE not found"

    # Check required env vars
    source .env 2>/dev/null || true
    [ -z "${DB_PASSWORD:-}" ] && die "DB_PASSWORD not set in .env"
    [ -z "${APP_KEY:-}" ]     && die "APP_KEY not set in .env"

    success "Pre-flight checks passed"
}

# ── Database backup ────────────────────────────────────────────────────────────
backup_database() {
    log "Creating database backup ..."
    mkdir -p "$BACKUP_DIR"

    local timestamp
    timestamp=$(date '+%Y%m%d_%H%M%S')
    local backup_file="${BACKUP_DIR}/velour_db_${timestamp}.sql.gz"

    docker compose -f "$COMPOSE_FILE" exec -T postgres \
        pg_dump -U "${DB_USERNAME:-velour}" "${DB_DATABASE:-velour_saas}" \
        | gzip > "$backup_file" \
        || die "Database backup failed"

    # Keep last 10 backups
    ls -t "${BACKUP_DIR}"/velour_db_*.sql.gz 2>/dev/null | tail -n +11 | xargs rm -f 2>/dev/null || true

    success "Database backup: ${backup_file}"
    echo "$backup_file"
}

# ── Save current image for rollback ───────────────────────────────────────────
save_rollback_ref() {
    local current_image
    current_image=$(docker compose -f "$COMPOSE_FILE" images -q "$APP_SERVICE" 2>/dev/null | head -1 || echo "")
    if [ -n "$current_image" ]; then
        echo "$current_image" > "$ROLLBACK_IMAGE_FILE"
        log "Rollback ref saved: ${current_image:0:12}"
    fi
}

# ── Pull new image ─────────────────────────────────────────────────────────────
pull_image() {
    log "Pulling image: ${VELOUR_IMAGE:-build}:${DEPLOY_TAG} ..."

    if [ -n "${VELOUR_IMAGE:-}" ]; then
        docker pull "${VELOUR_IMAGE}:${DEPLOY_TAG}" || die "Image pull failed"
        export VELOUR_IMAGE="${VELOUR_IMAGE}:${DEPLOY_TAG}"
    else
        log "No VELOUR_IMAGE set — building locally ..."
        docker compose -f "$COMPOSE_FILE" build "$APP_SERVICE" \
            || die "Docker build failed"
    fi
    success "Image ready"
}

# ── Run migrations ─────────────────────────────────────────────────────────────
run_migrations() {
    log "Running database migrations ..."
    docker compose -f "$COMPOSE_FILE" run --rm \
        --entrypoint="" \
        "$APP_SERVICE" \
        php artisan migrate --force --no-interaction \
        || die "Migrations failed"
    success "Migrations complete"
}

# ── Rolling restart (zero downtime) ───────────────────────────────────────────
rolling_restart() {
    log "Starting rolling restart ..."

    # Scale up new container alongside existing one
    docker compose -f "$COMPOSE_FILE" up -d \
        --scale "${APP_SERVICE}=2" \
        --no-recreate \
        "$APP_SERVICE" 2>/dev/null || true

    sleep 10  # Allow new container to boot and pass healthcheck

    # Replace with clean single instance
    docker compose -f "$COMPOSE_FILE" up -d \
        --force-recreate \
        --remove-orphans \
        "$APP_SERVICE" \
        || die "Container restart failed"

    # Reload nginx without dropping connections
    docker compose -f "$COMPOSE_FILE" exec nginx \
        nginx -s reload 2>/dev/null || true

    success "Containers restarted"
}

# ── Restart queue workers ──────────────────────────────────────────────────────
restart_queue() {
    log "Restarting queue workers via Horizon ..."
    docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" \
        php artisan horizon:terminate 2>/dev/null || true
    sleep 3
    success "Queue workers signalled"
}

# ── Health check ───────────────────────────────────────────────────────────────
health_check() {
    log "Running health check against ${HEALTH_URL} ..."
    local max=12
    local i=0

    until curl -sf --max-time 5 "$HEALTH_URL" >/dev/null 2>&1; do
        i=$((i+1))
        [ $i -ge $max ] && die "Health check failed after $((max*5))s — run: ./scripts/rollback.sh"
        echo -n "."
        sleep 5
    done
    echo ""
    success "Health check passed"
}

# ── Clear caches ───────────────────────────────────────────────────────────────
clear_caches() {
    log "Rebuilding application caches ..."
    docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" \
        php artisan optimize:clear >/dev/null 2>&1 || true
    docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" \
        php artisan optimize >/dev/null 2>&1 || true
    success "Caches rebuilt"
}

# ── Main ──────────────────────────────────────────────────────────────────────
main() {
    echo -e "${BOLD}══════════════════════════════════════════════════════${RESET}"
    echo -e "${BOLD}  Velour Deploy — $(date '+%Y-%m-%d %H:%M:%S')${RESET}"
    echo -e "${BOLD}══════════════════════════════════════════════════════${RESET}"

    notify_slack "🚀 Deploy starting: tag=${DEPLOY_TAG} host=$(hostname)"

    preflight
    save_rollback_ref
    pull_image
    backup_database
    run_migrations
    rolling_restart
    restart_queue
    health_check
    clear_caches

    local duration=$SECONDS
    echo -e "\n${GREEN}${BOLD}══════════════════════════════════════════════════════${RESET}"
    echo -e "${GREEN}${BOLD}  ✓ Deploy complete in ${duration}s${RESET}"
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════${RESET}\n"

    notify_slack "✅ Deploy complete: tag=${DEPLOY_TAG} (${duration}s)"
}

main "$@"
