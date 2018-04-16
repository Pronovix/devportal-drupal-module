<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Meta Parameters.
 *
 * This extends the base storage class, adding required special handling for
 * API Meta Parameters.
 *
 * @ingroup devportal_api_entities
 */
interface APIMetaParamStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Meta Parameter revision IDs for a specific API Meta Parameter.
   *
   * @param \Drupal\devportal_api_entities\APIMetaParamInterface $entity
   *   The API Meta Parameter entity.
   *
   * @return int[]
   *   API Meta Parameter revision IDs (in ascending order).
   */
  public function revisionIds(APIMetaParamInterface $entity);

}
