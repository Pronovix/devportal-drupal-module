<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;
use Michelf\Markdown;
use Michelf\MarkdownInterface;

/**
 * Converts Markdown into HTML.
 *
 * @MigrateProcessPlugin(
 *   id = "markdown",
 *   label = @Translation("Markdown"),
 *   contentProcessor = "markdown",
 *   formatHelp = "https://daringfireball.net/projects/markdown/syntax",
 * )
 */
class MarkdownProcess extends PluginBase implements MigrateProcessInterface {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $markdown = $this->markdownFactory();
    return $markdown->transform($value);
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }

  /**
   * Creates a new instance of the markdown processor.
   *
   * @return MarkdownInterface
   */
  protected function markdownFactory() {
    return new Markdown();
  }

}
