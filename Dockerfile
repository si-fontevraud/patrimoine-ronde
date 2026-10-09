# syntax=docker/dockerfile:1.7

FROM php:8.4-fpm-bookworm AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        zip \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        pdo_pgsql \
        opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM base AS dependencies

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --no-plugins \
    --no-dev \
    --optimize-autoloader \
    --classmap-authoritative

FROM base AS app_prod

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

COPY --from=dependencies /var/www/html/vendor ./vendor
COPY . .

RUN composer dump-autoload --no-dev --classmap-authoritative --optimize \
    && php bin/console importmap:install --env=prod --no-debug \
    && php bin/console cache:clear --env=prod --no-debug \
    && php bin/console asset-map:compile --env=prod --no-debug \
    && mkdir -p var/cache var/log var/share \
    && chown -R www-data:www-data var \
    && chmod -R 0775 var

COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

USER www-data

ENTRYPOINT ["/usr/local/bin/app-entrypoint"]
CMD ["php-fpm"]

FROM nginx:1.28-alpine AS nginx

WORKDIR /var/www/html

COPY --from=app_prod /var/www/html/public ./public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
