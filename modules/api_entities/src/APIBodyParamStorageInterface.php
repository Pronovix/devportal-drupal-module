<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Body Params.
 *
 * This extends the base storage class, adding required special handling for
 * API Body Params.
 *
 * @ingroup devportal_api_entities
 */
interface APIBodyParamStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Body Param revision IDs for a specific API Body Param.
   *
   * @param \Drupal\devportal_api_entities\APIBodyParamInterface $entity
   *   The API Body Param entity.
   *
   * @return int[]
   *   API Body Param revision IDs (in ascending order).
   */
  public function revisionIds(APIBodyParamInterface $entity);

}
