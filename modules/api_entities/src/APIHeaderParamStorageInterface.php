<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Header Params.
 *
 * This extends the base storage class, adding required special handling for
 * API Header Params.
 *
 * @ingroup devportal_api_entities
 */
interface APIHeaderParamStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Header Param revision IDs for a specific API Header Param.
   *
   * @param \Drupal\devportal_api_entities\APIHeaderParamInterface $entity
   *   The API Header Param entity.
   *
   * @return int[]
   *   API Header Param revision IDs (in ascending order).
   */
  public function revisionIds(APIHeaderParamInterface $entity);

}
