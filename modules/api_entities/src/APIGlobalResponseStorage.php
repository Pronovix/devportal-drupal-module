<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Global Responses.
 *
 * This extends the base storage class, adding required special handling for
 * API Global Responses.
 *
 * @ingroup devportal_api_entities
 */
class APIGlobalResponseStorage extends SqlContentEntityStorage implements APIGlobalResponseStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIGlobalResponseInterface $entity) {
    return $this->database->select('api_global_response_revision', 'agrr')
      ->fields('agrr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
