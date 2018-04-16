<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Path Params.
 *
 * This extends the base storage class, adding required special handling for
 * API Path Params.
 *
 * @ingroup devportal_api_entities
 */
class APIPathParamStorage extends SqlContentEntityStorage implements APIPathParamStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIPathParamInterface $entity) {
    return $this->database->select('api_path_param_revision', 'appr')
      ->fields('appr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
