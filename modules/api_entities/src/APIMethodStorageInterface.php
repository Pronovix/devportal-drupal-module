<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Methods.
 *
 * This extends the base storage class, adding required special handling for
 * API Methods.
 *
 * @ingroup devportal_api_entities
 */
interface APIMethodStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Method revision IDs for a specific API Method.
   *
   * @param \Drupal\devportal_api_entities\APIMethodInterface $entity
   *   The API Method entity.
   *
   * @return int[]
   *   API Method revision IDs (in ascending order).
   */
  public function revisionIds(APIMethodInterface $entity);

}
