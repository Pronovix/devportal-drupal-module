<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Param Items.
 *
 * This extends the base storage class, adding required special handling for
 * API Param Items.
 *
 * @ingroup devportal_api_entities
 */
interface APIParamItemStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Param Item revision IDs for a specific API Param Item.
   *
   * @param \Drupal\devportal_api_entities\APIParamItemInterface $entity
   *   The API Param Item entity.
   *
   * @return int[]
   *   API Param Item revision IDs (in ascending order).
   */
  public function revisionIds(APIParamItemInterface $entity);

}
