#!/bin/bash
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

npm install
npm run build

php artisan config:clear
php artisan route:clear

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
