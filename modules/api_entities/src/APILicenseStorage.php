<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Licenses.
 *
 * This extends the base storage class, adding required special handling for
 * API Licenses.
 *
 * @ingroup devportal_api_entities
 */
class APILicenseStorage extends SqlContentEntityStorage implements APILicenseStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APILicenseInterface $entity) {
    return $this->database->select('api_license_revision', 'alr')
      ->fields('alr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
