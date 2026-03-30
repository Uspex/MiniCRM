#!/bin/bash
set -e

cd "$(dirname "$0")"

# Читаем .env для подключения к БД
source <(grep -E '^DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=' .env | sed 's/^/export /')

BACKUP_DIR="storage/backups"
BACKUP_FILE="${BACKUP_DIR}/backup_$(date +%Y%m%d_%H%M%S).sql.gz"

echo "=== Database backup ==="
mysqldump --default-character-set=utf8mb4 -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" | gzip > "${BACKUP_FILE}"
echo "Backup saved: ${BACKUP_FILE}"

# Ротация: оставляем только 5 последних бекапов
echo "=== Cleanup old backups ==="
ls -1t ${BACKUP_DIR}/backup_*.sql.gz 2>/dev/null | tail -n +6 | xargs -r rm -f
echo "Backups kept: $(ls -1 ${BACKUP_DIR}/backup_*.sql.gz 2>/dev/null | wc -l)"

echo "=== Git pull ==="
git pull origin master

echo "=== Composer install ==="
composer install --no-dev --optimize-autoloader

echo "=== Migrations ==="
php artisan migrate --force

echo "=== Seeding ==="
php artisan db:seed --force

echo "=== Cache clear ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Done ==="
