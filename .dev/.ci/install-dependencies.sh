#!/usr/bin/env bash

set -e

# We need to run both "install" and "update" commands because:
# * `--prefer-lowest` is not supported by "install".
# * it seems there is an issue with the merge plugin and because of that if we would only run
# `composer update --prefer-lowest` then incorrect lower versions could be installed, ex.: drupal/core:8.5.0 where
# there is a drupal/core: ^8.7 constraint.
docker-compose exec php composer install ${COMPOSER_GLOBAL_OPTIONS}
if [[ -n "${DEPENDENCIES}" ]]; then
    docker-compose exec php composer update ${COMPOSER_GLOBAL_OPTIONS} ${DEPENDENCIES} --with-dependencies
else
    # Ensure Drupal coding standard is registered.
    # TODO Check why it gets immediately unregistered after it has been registered
    # Error:
    # PHP CodeSniffer Config installed_paths set to ../../drupal/coder/coder_sniffer
    # PHP CodeSniffer Config installed_paths delete
    docker-compose exec php composer update none
fi
# Log the installed versions.
docker-compose exec php ./vendor/bin/run composer:debug
