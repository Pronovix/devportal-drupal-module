<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Form Data Params.
 *
 * This extends the base storage class, adding required special handling for
 * API Form Data Params.
 *
 * @ingroup devportal_api_entities
 */
interface APIFormDataParamStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Form Data Param revision IDs for a specific API Form Data Param.
   *
   * @param \Drupal\devportal_api_entities\APIFormDataParamInterface $entity
   *   The API Form Data Param entity.
   *
   * @return int[]
   *   API Form Data Param revision IDs (in ascending order).
   */
  public function revisionIds(APIFormDataParamInterface $entity);

}
