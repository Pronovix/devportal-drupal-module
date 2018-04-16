<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Docs.
 *
 * This extends the base storage class, adding required special handling for
 * API Docs.
 *
 * @ingroup devportal_api_entities
 */
class APIDocStorage extends SqlContentEntityStorage implements APIDocStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIDocInterface $entity) {
    return $this->database->select('api_doc_revision', 'adr')
      ->fields('adr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
