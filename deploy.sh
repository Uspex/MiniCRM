#!/bin/bash
set -e

cd "$(dirname "$0")"

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
