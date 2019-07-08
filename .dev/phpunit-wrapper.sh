#!/usr/bin/env bash

set -e

# Register shared variables.
THREADS=${THREADS:-4}

PHPUNIT="./vendor/bin/phpunit -c build/core -v --debug --printer \Drupal\Tests\Listeners\HtmlOutputPrinter"

# Do not exit if any PHPUnit test fails.
set +e

# If no argument passed start the testrunner and start running ALL tests in the module concurrently, otherwise pass
# them directly to PHPUnit.
if [[ $# -eq 0 ]]; then
    # .dev directory must be excluded from the lookup, unfortunately the
    # "^((?!build\/modules\/drupal_module\/(.dev|vendor)).)*Test.php$" pattern does not work with Go's regexp
    # implementation as "-pattern".
  ./testrunner -verbose -threads=${THREADS} -root=build/modules/drupal_module/tests -command="$PHPUNIT"
  if [[ -d build/modules/drupal_module/modules ]]; then ./testrunner -verbose -threads=${THREADS} -root=build/modules/drupal_module/modules -command="$PHPUNIT"; fi
else
   # drupal-skip-dev-env-from-extension-discovery-in-testing.patch ensures the .dev directory is excluded from
   # scanning.
   ${PHPUNIT} ${@}
fi
