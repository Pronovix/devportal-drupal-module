<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Body Params.
 *
 * This extends the base storage class, adding required special handling for
 * API Body Params.
 *
 * @ingroup devportal_api_entities
 */
class APIBodyParamStorage extends SqlContentEntityStorage implements APIBodyParamStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIBodyParamInterface $entity) {
    return $this->database->select('api_body_param_revision', 'abpr')
      ->fields('abpr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
