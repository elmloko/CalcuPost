FROM node:22-bookworm-slim AS node_runtime

FROM php:8.4-apache-bookworm AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libatomic1 \
        libstdc++6 \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd zip exif pcntl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Laravel sirve desde /public, no desde la raíz del repo
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

FROM runtime AS development
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node_runtime /usr/local /usr/local
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

FROM runtime AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

FROM node_runtime AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit
COPY resources ./resources
COPY --from=dependencies /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
COPY vite.config.js tailwind.config.js postcss.config.cjs ./
RUN npm run build

FROM runtime AS production
ENV APP_ENV=production APP_DEBUG=false
COPY --from=dependencies /var/www/html /var/www/html
COPY --from=frontend /app/public/build ./public/build
COPY docker-production-entrypoint.sh /usr/local/bin/docker-production-entrypoint.sh
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && chmod +x /usr/local/bin/docker-production-entrypoint.sh \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 80
ENTRYPOINT ["docker-production-entrypoint.sh"]
CMD ["apache2-foreground"]
