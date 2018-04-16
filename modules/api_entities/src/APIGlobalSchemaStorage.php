<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Global Schemas.
 *
 * This extends the base storage class, adding required special handling for
 * API Global Schemas.
 *
 * @ingroup devportal_api_entities
 */
class APIGlobalSchemaStorage extends SqlContentEntityStorage implements APIGlobalSchemaStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIGlobalSchemaInterface $entity) {
    return $this->database->select('api_global_schema_revision', 'agsr')
      ->fields('agsr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
