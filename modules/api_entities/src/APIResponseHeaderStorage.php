<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Response Headers.
 *
 * This extends the base storage class, adding required special handling for
 * API Response Headers.
 *
 * @ingroup devportal_api_entities
 */
class APIResponseHeaderStorage extends SqlContentEntityStorage implements APIResponseHeaderStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIResponseHeaderInterface $entity) {
    return $this->database->select('api_response_header_revision', 'arhr')
      ->fields('arhr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
