
# ==========================================
# STAGE 1 - DEPENDENCIAS PHP / COMPOSER
# ==========================================
FROM php:8.4-cli-bookworm AS vendor

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libpq-dev libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install -j$(nproc) \
       pdo_pgsql pgsql mbstring intl zip bcmath pcntl dom xml \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

COPY . .

RUN composer dump-autoload \
    --no-dev \
    --classmap-authoritative \
    --no-interaction


# ==========================================
# STAGE 2 - FRONTEND / VITE
# ==========================================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm install --no-audit --no-fund

COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public ./public

RUN npm run build


# ==========================================
# STAGE 3 - LARAVEL / PHP 8.4 / NGINX
# ==========================================
FROM php:8.4-fpm-bookworm AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    curl \
    && docker-php-ext-install -j$(nproc) \
       pdo_pgsql \
       pgsql \
       mbstring \
       intl \
       zip \
       bcmath \
       pcntl \
       opcache \
       dom \
       xml \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Arquivos da aplicacao
COPY . .

# Dependencias PHP
COPY --from=vendor /app/vendor ./vendor

# Assets compilados
COPY --from=assets /app/public/build ./public/build

# Configuracoes
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini
COPY docker/entrypoint.sh /usr/local/bin/carwash-entrypoint

# Permissoes e diretorios
RUN chmod +x /usr/local/bin/carwash-entrypoint \
    && mkdir -p /run/nginx \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["carwash-entrypoint"]

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
