<?php

namespace Drupal\devportal_api_reference\Plugin\Reference;

use Drupal\Core\Annotation\Translation;
use Drupal\devportal_api_reference\ReferenceInterface;

/**
 * @Reference(
 *   id = "empty",
 *   label = @Translation("Dummy reference plugin"),
 *   extensions = {},
 *   weight = 1,
 * )
 */
class EmptyReference extends ReferenceBase implements ReferenceInterface {

  /**
   * {@inheritdoc}
   */
  public function getVersion(?\stdClass $doc): ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function parse(string $file_path): ?\stdClass {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(\stdClass $content) {
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(?\stdClass $doc): ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(?\stdClass $doc): ?string {
    return NULL;
  }

}
