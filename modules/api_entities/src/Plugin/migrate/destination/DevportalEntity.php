<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\destination;

use Drupal\devportal_api_entities\Entity\APIVersionTag;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;

/**
 * Provides Devportal Docs entity destination plugin.
 *
 * @MigrateDestination(
 *   id = "devportal_api_entities_entity",
 *   deriver = "Drupal\devportal_api_entities\Plugin\Derivative\DevportalMigrateEntity"
 * )
 */
class DevportalEntity extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    return substr($plugin_id, strlen('devportal_api_entities_entity:'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntity(Row $row, array $old_destination_id_values) {
    // Load the current API Version Tag object for revision log.
    $api_version_tag = $row->getDestinationProperty('api_version_tag');
    $api_version_tag = APIVersionTag::load(reset($api_version_tag));

    $entity_id = reset($old_destination_id_values) ?: $this->getEntityId($row);
    if (!empty($entity_id) && ($entity = $this->storage->load($entity_id))) {
      // Update the entity and create a new revision.
      $entity->enforceIsNew(FALSE);
      $entity->setNewRevision(TRUE);
      $entity->setRevisionUserId(1);
      $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      /** @var \Drupal\devportal_api_entities\Entity\APIVersionTag $api_version_tag */
      $entity->setRevisionLogMessage($this->t('Devportal Docs Migration - API version: @version', [
        '@version' => $api_version_tag->getName(),
      ]));

      $entity = $this->updateEntity($entity, $row) ?: $entity;
      // Do NOT make the new revision default, since this will be managed
      // manually somewhere from the UI (revert the whole API documentation to
      // a certain api version tag).
      // TODO: $entity->isDefaultRevision(FALSE);
    }
    else {
      // Attempt to ensure we always have a bundle.
      if ($bundle = $this->getBundle($row)) {
        $row->setDestinationProperty($this->getKey('bundle'), $bundle);
      }

      // Stubs might need some required fields filled in.
      if ($row->isStub()) {
        $this->processStubRow($row);
      }
      $entity = $this->storage->create($row->getDestination());
      $entity->enforceIsNew();

      $entity->setRevisionUserId(1);
      $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      /** @var \Drupal\devportal_api_entities\Entity\APIVersionTag $api_version_tag */
      $entity->setRevisionLogMessage($this->t('Devportal Docs Migration - API version: @version', [
        '@version' => $api_version_tag->getName(),
      ]));
    }

    return $entity;
  }

}
