<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Responses.
 *
 * This extends the base storage class, adding required special handling for
 * API Responses.
 *
 * @ingroup devportal_api_entities
 */
class APIResponseStorage extends SqlContentEntityStorage implements APIResponseStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIResponseInterface $entity) {
    return $this->database->select('api_response_revision', 'arr')
      ->fields('arr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
