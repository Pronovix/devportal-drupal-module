<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Schemas.
 *
 * This extends the base storage class, adding required special handling for
 * API Schemas.
 *
 * @ingroup devportal_api_entities
 */
class APISchemaStorage extends SqlContentEntityStorage implements APISchemaStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APISchemaInterface $entity) {
    return $this->database->select('api_schema_revision', 'asr')
      ->fields('asr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
