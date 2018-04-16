<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for API Contacts.
 *
 * This extends the base storage class, adding required special handling for
 * API Contacts.
 *
 * @ingroup devportal_api_entities
 */
class APIContactStorage extends SqlContentEntityStorage implements APIContactStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(APIContactInterface $entity) {
    return $this->database->select('api_contact_revision', 'acr')
      ->fields('acr', ['vid'])
      ->condition('id', $entity->id())
      ->orderBy('vid')
      ->execute()
      ->fetchCol();
  }

}
