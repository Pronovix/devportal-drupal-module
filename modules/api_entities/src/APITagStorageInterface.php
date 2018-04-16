<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Tags.
 *
 * This extends the base storage class, adding required special handling for
 * API Tags.
 *
 * @ingroup devportal_api_entities
 */
interface APITagStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Tag revision IDs for a specific API Tag.
   *
   * @param \Drupal\devportal_api_entities\APITagInterface $entity
   *   The API Tag entity.
   *
   * @return int[]
   *   API Tag revision IDs (in ascending order).
   */
  public function revisionIds(APITagInterface $entity);

}
