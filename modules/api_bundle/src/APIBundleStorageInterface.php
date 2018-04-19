<?php

namespace Drupal\devportal_api_bundle;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Bundles.
 *
 * This extends the base storage class, adding required special handling for
 * API Bundles.
 *
 * @ingroup devportal_api_bundle
 */
interface APIBundleStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Bundle revision IDs for a specific API Bundle.
   *
   * @param \Drupal\devportal_api_bundle\APIBundleInterface $entity
   *   The API Bundle entity.
   *
   * @return int[]
   *   API Bundle revision IDs (in ascending order).
   */
  public function revisionIds(APIBundleInterface $entity);

}
