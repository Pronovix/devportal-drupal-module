#!/usr/bin/env bash

set -e

if [[ ${RUN_BEHAT_TESTS} == true ]]; then
    .ci/install-dependencies.sh
    # Install the site with the minimal profile for running Behat tests.
    docker-compose exec php ./vendor/bin/run behat:setup
    # Run all tests.
    docker-compose exec php ./vendor/bin/behat
fi
