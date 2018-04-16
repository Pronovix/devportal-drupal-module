<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Tags.
 *
 * This extends the base storage class, adding required special handling for
 * API Tags.
 *
 * @ingroup devportal_api_entities
 */
class APITagStorage extends SqlContentEntityStorage implements APITagStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APITagInterface $entity) {
    return $this->database->select('api_tag_revision', 'atr')
      ->fields('atr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
