<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Form Data Params.
 *
 * This extends the base storage class, adding required special handling for
 * API Form Data Params.
 *
 * @ingroup devportal_api_entities
 */
class APIFormDataParamStorage extends SqlContentEntityStorage implements APIFormDataParamStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIFormDataParamInterface $entity) {
    return $this->database->select('api_form_data_param_revision', 'afdpr')
      ->fields('afdpr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
