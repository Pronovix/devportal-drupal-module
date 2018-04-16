<?php

namespace Drupal\devportal_repo_sync\Plugin;

/**
 * Contains HTML processing functions.
 */
trait HTMLProcessorTrait {

  /**
   * Parses $content and calls $processor on it.
   *
   * @param string $content
   *   HTML fragment
   * @param callable $processor
   *   Processor function. It takes an argument which is a \DOMDocument and
   *   returns either the processed content or NULL. If NULL is returned, then
   *   the \DOMDocument will be converted back to string and returned.
   *
   * @return string
   *   Processed HTML fragment in string.
   */
  protected static function withHTML($content, callable $processor) {
    $doc = new \DOMDocument();
    $doc->loadHTML("<html><head></head><body>{$content}</body></html>");

    $processed = $processor($doc);

    if ($processed === NULL) {
      return static::innerHTML($doc->getElementsByTagName('body')[0]);
    }

    return $processed;
  }

  /**
   * Converts the inner html of a \DOMNode into a string.
   *
   * @param \DOMNode $node
   *
   * @return string
   */
  protected static function innerHTML(\DOMNode $node) {
    $content = '';
    foreach ($node->childNodes as $child) {
      /** @var \DOMElement $child */
      $content .= $child->ownerDocument->saveHTML($child);
    }

    return $content;
  }

}
