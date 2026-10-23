#!/bin/bash
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer_lock_hash=$(sha256sum composer.lock | cut -d ' ' -f1)
if [ ! -f vendor/autoload.php ] || [ "$(cat vendor/.composer-lock.sha256 2>/dev/null || true)" != "$composer_lock_hash" ]; then
    composer install --no-interaction --prefer-dist --no-progress
    printf '%s' "$composer_lock_hash" > vendor/.composer-lock.sha256
fi

if [ -z "${APP_KEY:-}" ]; then
    # An empty container variable must not shadow the generated local key.
    unset APP_KEY
fi
if [ -z "${APP_KEY:-}" ] && ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --force
fi

package_lock_hash=$(sha256sum package-lock.json | cut -d ' ' -f1)
if [ ! -d node_modules/vite ] || [ "$(cat node_modules/.package-lock.sha256 2>/dev/null || true)" != "$package_lock_hash" ]; then
    npm ci --no-audit
    printf '%s' "$package_lock_hash" > node_modules/.package-lock.sha256
fi
npm run build

php artisan config:clear
php artisan route:clear

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
