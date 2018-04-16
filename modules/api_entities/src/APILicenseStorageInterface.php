<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Licenses.
 *
 * This extends the base storage class, adding required special handling for
 * API Licenses.
 *
 * @ingroup devportal_api_entities
 */
interface APILicenseStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API License revision IDs for a specific API License.
   *
   * @param \Drupal\devportal_api_entities\APILicenseInterface $entity
   *   The API License entity.
   *
   * @return int[]
   *   API License revision IDs (in ascending order).
   */
  public function revisionIds(APILicenseInterface $entity);

}
