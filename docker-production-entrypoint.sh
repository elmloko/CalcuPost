#!/bin/bash
set -euo pipefail

# Secrets are supplied at runtime; a restart must never rotate encryption keys.
: "${APP_KEY:?Set a persistent APP_KEY before starting production.}"
export APP_ENV=production APP_DEBUG=false

mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
