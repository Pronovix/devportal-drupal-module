<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Schemas.
 *
 * This extends the base storage class, adding required special handling for
 * API Schemas.
 *
 * @ingroup devportal_api_entities
 */
interface APISchemaStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Schema revision IDs for a specific API Schema.
   *
   * @param \Drupal\devportal_api_entities\APISchemaInterface $entity
   *   The API Schema entity.
   *
   * @return int[]
   *   API Schema revision IDs (in ascending order).
   */
  public function revisionIds(APISchemaInterface $entity);

}
