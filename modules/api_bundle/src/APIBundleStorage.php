<?php

namespace Drupal\devportal_api_bundle;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Bundles.
 *
 * This extends the base storage class, adding required special handling for
 * API Bundles.
 *
 * @ingroup devportal_api_bundle
 */
class APIBundleStorage extends SqlContentEntityStorage implements APIBundleStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIBundleInterface $entity) {
    return $this->database->select('api_bundle_revision', 'abr')
      ->fields('abr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
