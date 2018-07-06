<?php

namespace Drupal\devportal_repo_sync\Plugin\FileType;

use Drupal\Core\Entity\EntityInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @FileType(
 *   id = "picture",
 *   label = "Picture",
 *   matcher = "#\.(jpe?g|tiff?|gif|bmp|png|webp)$#",
 *   weight = 0,
 * )
 */
class Picture extends FileTypeBase {

  /**
   * {@inheritdoc}
   */
  public function import(?EntityInterface $entity, string $filename, string $prefix, StreamInterface $content): ?EntityInterface {
    if ($entity === NULL) {
      return $this->saveFile($filename, $prefix, $content);
    }

    if ($this->ensureFile($filename, $prefix, $content) === NULL) {
      return NULL;
    }

    $entity->save();

    return $entity;
  }

}
