<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Endpoint Sets.
 *
 * This extends the base storage class, adding required special handling for
 * API Endpoint Sets.
 *
 * @ingroup devportal_api_entities
 */
interface APIEndpointSetStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Endpoint Set revision IDs for a specific API Endpoint Set.
   *
   * @param \Drupal\devportal_api_entities\APIEndpointSetInterface $entity
   *   The API Endpoint Set entity.
   *
   * @return int[]
   *   API Endpoint Set revision IDs (in ascending order).
   */
  public function revisionIds(APIEndpointSetInterface $entity);

}
