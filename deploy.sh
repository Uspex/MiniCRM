#!/bin/bash
set -e

cd "$(dirname "$0")"

# Читаем .env для подключения к БД
source <(grep -E '^DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=' .env | sed 's/^/export /')

BACKUP_DIR="storage/backups"
BACKUP_FILE="${BACKUP_DIR}/backup_$(date +%Y%m%d_%H%M%S).sql.gz"

echo "=== Database backup ==="
echo "  Host: ${DB_HOST}:${DB_PORT}"
echo "  User: ${DB_USERNAME}"
echo "  Database: ${DB_DATABASE}"
echo "  File: ${BACKUP_FILE}"

mkdir -p "${BACKUP_DIR}"

if ! DUMP_ERROR=$(mysqldump --default-character-set=utf8mb4 -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" 2>&1 | gzip > "${BACKUP_FILE}"); then
    echo "ERROR: mysqldump failed:"
    echo "${DUMP_ERROR}"
    rm -f "${BACKUP_FILE}"
    exit 1
fi

# Проверяем что файл не пустой (gzip пустого потока ~20 байт)
if [ ! -s "${BACKUP_FILE}" ] || [ "$(stat -c%s "${BACKUP_FILE}" 2>/dev/null || stat -f%z "${BACKUP_FILE}" 2>/dev/null)" -lt 100 ]; then
    echo "ERROR: backup file is empty or too small, dump likely failed"
    rm -f "${BACKUP_FILE}"
    exit 1
fi

echo "Backup saved: ${BACKUP_FILE} ($(du -h "${BACKUP_FILE}" | cut -f1))"

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
