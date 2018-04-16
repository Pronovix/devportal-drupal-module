<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Endpoints.
 *
 * This extends the base storage class, adding required special handling for
 * API Endpoints.
 *
 * @ingroup devportal_api_entities
 */
class APIEndpointStorage extends SqlContentEntityStorage implements APIEndpointStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIEndpointInterface $entity) {
    return $this->database->select('api_endpoint_revision', 'aer')
      ->fields('aer', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
