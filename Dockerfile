# syntax=docker/dockerfile:1.7

FROM php:8.4-fpm-bookworm AS app_base

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl pdo_pgsql opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM app_base AS app_prod

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-scripts

COPY . .

RUN APP_SECRET=build-secret php bin/console asset-map:compile --env=prod --no-debug \
    && rm -rf var/cache/* var/log/* \
    && mkdir -p var/cache var/log var/share \
    && chown -R www-data:www-data var

COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

USER www-data

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]

FROM nginx:1.28-alpine AS nginx

WORKDIR /var/www/html

COPY --from=app_prod /var/www/html/public ./public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
