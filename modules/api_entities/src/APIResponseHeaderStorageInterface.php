<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Response Headers.
 *
 * This extends the base storage class, adding required special handling for
 * API Response Headers.
 *
 * @ingroup devportal_api_entities
 */
interface APIResponseHeaderStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Response Header revision IDs for a specific API Response Header.
   *
   * @param \Drupal\devportal_api_entities\APIResponseHeaderInterface $entity
   *   The API Response Header entity.
   *
   * @return int[]
   *   API Response Header revision IDs (in ascending order).
   */
  public function revisionIds(APIResponseHeaderInterface $entity);

}
