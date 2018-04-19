<?php

namespace Drupal\devportal_api_bundle\Form;

use Drupal\Core\Entity\EntityDeleteForm;

/**
 * Provides a confirmation form for deleting a DHL entity type.
 */
class DHLAPIEntitiesEntityDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  function getDeletionMessage() {
    $entity = $this->getEntity();
    return $this->t('The @entity_type %label has been deleted.', [
      '@entity_type' => $entity->getEntityType()->getLabel(),
      '%label' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  function getQuestion() {
    $entity = $this->getEntity();
    return $this->t('Are you sure you want to delete the @entity_type %label?', [
      '@entity_type' => $entity->getEntityType()->getLabel(),
      '%label' => $entity->label(),
    ]);
  }

}
