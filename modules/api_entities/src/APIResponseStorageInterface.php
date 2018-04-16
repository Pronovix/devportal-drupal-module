<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Responses.
 *
 * This extends the base storage class, adding required special handling for
 * API Responses.
 *
 * @ingroup devportal_api_entities
 */
interface APIResponseStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Response revision IDs for a specific API Response.
   *
   * @param \Drupal\devportal_api_entities\APIResponseInterface $entity
   *   The API Response entity.
   *
   * @return int[]
   *   API Response revision IDs (in ascending order).
   */
  public function revisionIds(APIResponseInterface $entity);

}
