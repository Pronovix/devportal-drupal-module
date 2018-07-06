<?php

namespace Drupal\devportal_repo_sync;

use Drupal\Core\Entity\EntityInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Interface for the FileType plugins.
 */
interface FileTypeInterface {

  /**
   * Imports a file into an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   Existing entity if applicable.
   * @param string $filename
   *   The name of the file.
   * @param string $prefix
   *   Path prefix for the file.
   * @param \Psr\Http\Message\StreamInterface $content
   *   The contents of the file.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity with the new data if success, null on failure.
   */
  public function import(?EntityInterface $entity, string $filename, string $prefix, StreamInterface $content): ?EntityInterface;

}
