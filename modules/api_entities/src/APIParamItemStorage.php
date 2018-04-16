<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Param Items.
 *
 * This extends the base storage class, adding required special handling for
 * API Param Items.
 *
 * @ingroup devportal_api_entities
 */
class APIParamItemStorage extends SqlContentEntityStorage implements APIParamItemStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIParamItemInterface $entity) {
    return $this->database->select('api_param_item_revision', 'apir')
      ->fields('apir', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
