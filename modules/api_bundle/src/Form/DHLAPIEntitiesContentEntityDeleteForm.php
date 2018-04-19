<?php

namespace Drupal\devportal_api_bundle\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a confirmation form for deleting a DHL content entity.
 */
class DHLAPIEntitiesContentEntityDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDeletionMessage() {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity();
    $entity_type = $entity->getEntityType();

    // Check if the entity is bundleable or not.
    if ($bundle_entity_type = $entity_type->get('bundle_entity_type')) {
      $entity_bundle = \Drupal::entityTypeManager()->getStorage($bundle_entity_type)->load($entity->bundle());
      if (!$entity->isDefaultTranslation()) {
        return $this->t('The @bundle %label @language translation has been deleted.', [
          '@bundle' => $entity_bundle ? $entity_bundle->label() : FALSE,
          '%label'       => $entity->label(),
          '@language'    => $entity->language()->getName(),
        ]);
      }
      return $this->t('The @bundle %label has been deleted.', [
        '@bundle' => $entity_bundle ? $entity_bundle->label() : FALSE,
        '%label' => $entity->label(),
      ]);
    }

    if (!$entity->isDefaultTranslation()) {
      return $this->t('The @entity_type %label @language translation has been deleted.', [
        '@entity_type' => $entity_type->getLabel(),
        '%label'       => $entity->label(),
        '@language'    => $entity->language()->getName(),
      ]);
    }
    return $this->t('The @entity_type %label has been deleted.', [
      '@entity_type' => $entity_type->getLabel(),
      '%label' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity();
    $entity_type = $entity->getEntityType();

    // Check if the entity is bundleable or not.
    if ($bundle_entity_type = $entity_type->get('bundle_entity_type')) {
      $entity_bundle = \Drupal::entityTypeManager()->getStorage($bundle_entity_type)->load($entity->bundle());
      if (!$entity->isDefaultTranslation()) {
        return $this->t('Are you sure you want to delete the @language translation of the @bundle %label?', [
          '@language' => $entity->language()->getName(),
          '@bundle' => $entity_bundle ? $entity_bundle->label() : FALSE,
          '%label' => $entity->label(),
        ]);
      }
      return $this->t('Are you sure you want to delete the @bundle %label?', [
        '@bundle' => $entity_bundle ? $entity_bundle->label() : FALSE,
        '%label' => $entity->label(),
      ]);
    }

    if (!$entity->isDefaultTranslation()) {
      return $this->t('Are you sure you want to delete the @language translation of the @entity_type %label?', [
        '@language' => $entity->language()->getName(),
        '@entity_type' => $entity_type->getLabel(),
        '%label' => $entity->label(),
      ]);
    }
    return $this->t('Are you sure you want to delete the @entity_type %label?', [
      '@entity_type' => $entity_type->getLabel(),
      '%label' => $entity->label(),
    ]);
  }

}
