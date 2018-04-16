<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Response Examples.
 *
 * This extends the base storage class, adding required special handling for
 * API Response Examples.
 *
 * @ingroup devportal_api_entities
 */
interface APIResponseExampleStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Response Example revision IDs for a specific API Response Example.
   *
   * @param \Drupal\devportal_api_entities\APIResponseExampleInterface $entity
   *   The API Response Example entity.
   *
   * @return int[]
   *   API Response Example revision IDs (in ascending order).
   */
  public function revisionIds(APIResponseExampleInterface $entity);

}
