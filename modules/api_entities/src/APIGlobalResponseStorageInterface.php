<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Global Responses.
 *
 * This extends the base storage class, adding required special handling for
 * API Global Responses.
 *
 * @ingroup devportal_api_entities
 */
interface APIGlobalResponseStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Global Response revision IDs for a specific API Global Response.
   *
   * @param \Drupal\devportal_api_entities\APIGlobalResponseInterface $entity
   *   The API Global Response entity.
   *
   * @return int[]
   *   API Global Response revision IDs (in ascending order).
   */
  public function revisionIds(APIGlobalResponseInterface $entity);

}
