<?php

namespace Drupal\devportal_api_reference;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API References.
 *
 * This extends the base storage class, adding required special handling for
 * API References.
 *
 * @ingroup devportal_api_reference
 */
interface APIRefStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Reference revision IDs for a specific API Reference.
   *
   * @param \Drupal\devportal_api_reference\APIRefInterface $entity
   *   The API Reference entity.
   *
   * @return int[]
   *   API Reference revision IDs (in ascending order).
   */
  public function revisionIds(APIRefInterface $entity);

}
