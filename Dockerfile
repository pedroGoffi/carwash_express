FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
# package-lock legado pode estar fora de sincronia; npm install reconcilia a arvore e permite o build
RUN npm install --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public ./public
RUN npm run build

FROM php:8.3-fpm-bookworm AS runtime
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor libpq-dev libzip-dev libicu-dev libonig-dev libxml2-dev unzip curl \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql mbstring intl zip bcmath pcntl opcache dom xml \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini
COPY docker/entrypoint.sh /usr/local/bin/carwash-entrypoint
RUN chmod +x /usr/local/bin/carwash-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache \
    && mkdir -p /run/nginx

EXPOSE 80
ENTRYPOINT ["carwash-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
