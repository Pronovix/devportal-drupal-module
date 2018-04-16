<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\migrate\Plugin\MigrateProcessInterface;
use Michelf\MarkdownExtra;

/**
 * Converts Markdown Extra into HTML.
 *
 * @MigrateProcessPlugin(
 *   id = "markdownextra",
 *   label = @Translation("Markdown extra"),
 *   contentProcessor = "markdown",
 *   formatHelp = "https://michelf.ca/specs/markdown-extra/",
 * )
 */
class MarkdownExtraProcess extends MarkdownProcess implements MigrateProcessInterface {

  /**
   * {@inheritdoc}
   */
  protected function markdownFactory() {
    return new MarkdownExtra();
  }

}
