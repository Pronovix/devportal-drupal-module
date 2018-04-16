<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\Component\Utility\Unicode;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\devportal_repo_sync\Plugin\HTMLProcessorTrait;

/**
 * Extracts a title from HTML.
 *
 * @MigrateProcessPlugin(
 *   id = "extract_title"
 * )
 */
class ExtractTitleProcess extends ProcessPluginBase implements MigrateProcessInterface {

  /**
   * List of possible XPaths that contain a title.
   */
  const TITLE_XPATH_EXPRESSIONS = [
    '//h1',
    '//h2',
    '//h3',
    '//h4',
    '//h5',
    '//h6',
    '//p',
  ];

  use HTMLProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $element = static::withHTML($value, function (\DOMDocument $doc) {
      $xpath = new \DOMXPath($doc);

      foreach (static::TITLE_XPATH_EXPRESSIONS as $expr) {
        /** @var \DOMNodeList $result */
        $result = $xpath->evaluate($expr);
        if ($result && $result->length) {
          return $result->item(0);
        }
      }

      return FALSE;
    });

    if ($element instanceof \DOMNode) {
      return static::innerHTML($element);
    }
    else if ($element === FALSE) {
      $element = Unicode::truncate(strip_tags($value), 128, TRUE, TRUE);
    }

    return (string) $element;
  }

}
