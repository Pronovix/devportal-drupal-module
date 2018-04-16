<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Infos.
 *
 * This extends the base storage class, adding required special handling for
 * API Infos.
 *
 * @ingroup devportal_api_entities
 */
class APIInfoStorage extends SqlContentEntityStorage implements APIInfoStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIInfoInterface $entity) {
    return $this->database->select('api_info_revision', 'air')
      ->fields('air', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
