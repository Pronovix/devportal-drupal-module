<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Methods.
 *
 * This extends the base storage class, adding required special handling for
 * API Methods.
 *
 * @ingroup devportal_api_entities
 */
class APIMethodStorage extends SqlContentEntityStorage implements APIMethodStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIMethodInterface $entity) {
    return $this->database->select('api_method_revision', 'amr')
      ->fields('amr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
