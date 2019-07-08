#!/usr/bin/env bash

set -e

if [[ ${CHECK_CS} == true ]]; then
    .ci/install-dependencies.sh
    docker-compose exec php composer install ${COMPOSER_GLOBAL_OPTIONS}
    docker-compose exec php ./vendor/bin/run composer:debug
    # Run PHPCS, ignore warnings that could be false-positive.
    docker-compose exec php ./vendor/bin/phpcs --standard=../phpcs.xml -s -n --colors ..
fi
