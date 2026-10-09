#!/bin/sh
set -eu

if [ "${APP_PREPARE_ON_STARTUP:-1}" = "1" ]; then
    php bin/console cache:clear --env=prod --no-debug
    php bin/console cache:warmup --env=prod --no-debug
fi

if [ "${AUTO_MIGRATE:-0}" = "1" ]; then
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod
fi

exec "$@"
