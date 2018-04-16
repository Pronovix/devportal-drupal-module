<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Ext Docs.
 *
 * This extends the base storage class, adding required special handling for
 * API Ext Docs.
 *
 * @ingroup devportal_api_entities
 */
class APIExtDocStorage extends SqlContentEntityStorage implements APIExtDocStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIExtDocInterface $entity) {
    return $this->database->select('api_ext_doc_revision', 'aedr')
      ->fields('aedr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
