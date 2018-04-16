<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Ext Docs.
 *
 * This extends the base storage class, adding required special handling for
 * API Ext Docs.
 *
 * @ingroup devportal_api_entities
 */
interface APIExtDocStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Ext Doc revision IDs for a specific API Ext Doc.
   *
   * @param \Drupal\devportal_api_entities\APIExtDocInterface $entity
   *   The API Ext Doc entity.
   *
   * @return int[]
   *   API Ext Doc revision IDs (in ascending order).
   */
  public function revisionIds(APIExtDocInterface $entity);

}
