ARG PHP_IMAGE="wodby/drupal-php:7.2-dev"

FROM ${PHP_IMAGE}

USER root

RUN set -xe; \
  # Downgrade Composer to 1.7.3 because there is serious performance issue in >= 1.8.0.
  # https://github.com/composer/composer/issues/7051#issuecomment-504057405
  wget -qO- https://getcomposer.org/installer | php -- --version=1.7.3 --install-dir=/usr/local/bin --filename=composer;

USER wodby
