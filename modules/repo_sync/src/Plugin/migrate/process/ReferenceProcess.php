<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\devportal_repo_sync\Plugin\HTMLProcessorTrait;

/**
 * Toolkit class to work with links in HTML fragments.
 */
abstract class ReferenceProcess extends ProcessPluginBase implements MigrateProcessInterface {

  use HTMLProcessorTrait;

  /**
   * Calls $processor on all links in $content.
   *
   * @param string $content
   *   HTML fragment.
   * @param callable $processor
   *   A processing function that takes a string (the link itself) and a
   *   \DOMNode. It returns the new value for the link.
   *
   * @return string
   *   Processed content.
   */
  public static function withLinks($content, callable $processor) {
    return static::withHTML($content, function (\DOMDocument $doc) use ($processor) {
      foreach ($doc->getElementsByTagName('a') as $link) {
        /** @var \DOMElement $link */
        $href = $link->getAttribute('href');
        $processed_href = $processor($href, $link);
        $link->setAttribute('href', $processed_href);
      }

      foreach ($doc->getElementsByTagName('img') as $image) {
        /** @var \DOMElement $image */
        $src = $image->getAttribute('src');
        if ($src && strpos($src, 'data:') !== 0) {
          $processed_src = $processor($src, $image);
          $image->setAttribute('src', $processed_src);
        }

        $srcset = $image->getAttribute('srcset');
        if ($srcset) {
          $candidates = array_map('trim', explode(',', $srcset));
          foreach ($candidates as &$candidate) {
            $candidate = preg_replace('/([\s]+)(w[\d]+|[\d]+x)$/m', ' $2', $candidate);
            $last_space_pos = strrpos($candidate, ' ');
            if ($last_space_pos !== FALSE) {
              $imglink = substr($candidate, 0, $last_space_pos);
              $descriptor = substr($candidate, $last_space_pos);
              $imglink = $processor($imglink, $image);
              $candidate = $imglink . $descriptor;
            }
            else {
              $candidate = $processor($candidate, $image);
            }
          }

          $processed_srcset = implode(',', $candidates);
          $image->setAttribute('srcset', $processed_srcset);
        }
      }

      return NULL;
    });
  }

  /**
   * Resolves $link relative to $path.
   *
   * @param string $path
   *   The path of the file where $link is in.
   * @param string $link
   *   The link pointing to a different location.
   *
   * @return string
   *   Resolved link.
   */
  public static function resolveLink($path, $link) {
    $parts = explode('/', $path);
    array_pop($parts);
    $parts = array_merge($parts, explode('/', $link));
    $newParts = [];

    foreach ($parts as $part) {
      switch ($part) {
        case '':
        case '.':
          continue;

        case '..':
          array_pop($newParts);
          break;

        default:
          $newParts[] = $part;
          break;
      }
    }

    return implode('/', $newParts);
  }

}
