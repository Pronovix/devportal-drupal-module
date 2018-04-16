<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Query Params.
 *
 * This extends the base storage class, adding required special handling for
 * API Query Params.
 *
 * @ingroup devportal_api_entities
 */
interface APIQueryParamStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Query Param revision IDs for a specific API Query Param.
   *
   * @param \Drupal\devportal_api_entities\APIQueryParamInterface $entity
   *   The API Query Param entity.
   *
   * @return int[]
   *   API Query Param revision IDs (in ascending order).
   */
  public function revisionIds(APIQueryParamInterface $entity);

}
