#!/usr/bin/env bash

set -e

if [[ ${RUN_PHPUNIT_TESTS} == true ]]; then
    .ci/install-dependencies.sh
    # Be prepared for running tests.
    docker-compose exec php ./vendor/bin/run phpunit:setup
    # Run all tests parallel.
    docker-compose exec php ./phpunit-wrapper.sh
fi
