#!/usr/bin/env bash
# Velour — Database + storage backup
# Designed to be run by cron: 0 3 * * * /var/www/velour/scripts/backup.sh
set -euo pipefail

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/velour}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"
S3_BUCKET="${BACKUP_S3_BUCKET:-}"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')

mkdir -p "$BACKUP_DIR"
source .env 2>/dev/null || true

log() { echo "[$(date '+%H:%M:%S')] $*"; }

# Database backup
log "Starting database backup ..."
docker compose -f "$COMPOSE_FILE" exec -T postgres \
    pg_dump -U "${DB_USERNAME:-velour}" --no-password "${DB_DATABASE:-velour_saas}" \
    | gzip > "${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz"
log "✓ DB backup: ${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz"

# Storage backup
log "Starting storage backup ..."
docker run --rm \
    -v velour_app_storage:/source:ro \
    -v "${BACKUP_DIR}:/backup" \
    alpine tar czf "/backup/storage_${TIMESTAMP}.tar.gz" -C /source . 2>/dev/null || \
    log "⚠ Storage backup skipped (volume not found)"

# Upload to S3 if configured
if [ -n "$S3_BUCKET" ]; then
    log "Uploading to S3: s3://${S3_BUCKET}/backups/ ..."
    aws s3 cp "${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz" \
        "s3://${S3_BUCKET}/backups/db_${TIMESTAMP}.sql.gz" --quiet
    log "✓ S3 upload complete"
fi

# Prune old local backups
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +${RETENTION_DAYS} -delete 2>/dev/null || true
find "$BACKUP_DIR" -name "*.tar.gz" -mtime +${RETENTION_DAYS} -delete 2>/dev/null || true
log "✓ Pruned backups older than ${RETENTION_DAYS} days"

log "Backup complete."
