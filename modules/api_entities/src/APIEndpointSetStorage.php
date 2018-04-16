<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Endpoint Sets.
 *
 * This extends the base storage class, adding required special handling for
 * API Endpoint Sets.
 *
 * @ingroup devportal_api_entities
 */
class APIEndpointSetStorage extends SqlContentEntityStorage implements APIEndpointSetStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIEndpointSetInterface $entity) {
    return $this->database->select('api_endpoint_set_revision', 'aesr')
      ->fields('aesr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
