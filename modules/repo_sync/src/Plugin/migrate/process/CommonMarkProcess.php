<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;

/**
 * Converts Common Mark into HTML.
 *
 * @MigrateProcessPlugin(
 *   id = "commonmark",
 *   label = @Translation("Common Mark"),
 *   contentProcessor = "markdown",
 *   formatHelp = "http://commonmark.org/help/",
 * )
 */
class CommonMarkProcess extends PluginBase implements MigrateProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $environment = Environment::createCommonMarkEnvironment();
    $converter = new CommonMarkConverter([], $environment);
    $rendered = $converter->convertToHtml($value);

    return $rendered;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }

}
