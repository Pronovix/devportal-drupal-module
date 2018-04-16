<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the storage handler class for API Contacts.
 *
 * This extends the base storage class, adding required special handling for
 * API Contacts.
 *
 * @ingroup devportal_api_entities
 */
interface APIContactStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of API Contact revision IDs for a specific API Contact.
   *
   * @param \Drupal\devportal_api_entities\APIContactInterface $entity
   *   The API Contact entity.
   *
   * @return int[]
   *   API Contact revision IDs (in ascending order).
   */
  public function revisionIds(APIContactInterface $entity);

}
