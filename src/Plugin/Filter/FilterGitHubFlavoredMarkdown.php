<?php

namespace Drupal\devportal\Plugin\Filter;

use Parsedown;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a GitHub Flavored Markdown filter.
 *
 * @Filter(
 *   id = "filter_github_flavored_markdown",
 *   title = @Translation("GitHub Flavored Markdown"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   weight = 10
 * )
 */
class FilterGitHubFlavoredMarkdown extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $parsedown = new Parsedown();
    return new FilterProcessResult($parsedown->text($text));
  }

}
