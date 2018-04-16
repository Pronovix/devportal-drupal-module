<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;

interface AutoLabelInterface {

  /**
   * Gets the auto label.
   *
   * @return string
   *   The auto label value.
   */
  public function getAutoLabel();

  /**
   * Sets the auto label.
   *
   * @param string $auto_label
   *   The auto label value.
   *
   * @return $this
   */
  public function setAutoLabel($auto_label);

  /**
   * Provides auto label related base field definitions for an entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of base field definitions for the entity type, keyed by field
   *   name.
   *
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   */
  public static function autoLabelBaseFieldDefinitions(EntityTypeInterface $entity_type);

  /**
   * Acts on an entity before saving.
   */
  public function autoLabelPreSave();

  /**
   * Generates a label value for the auto label field.
   *
   * @return string
   *   The generated label value.
   */
  public function generateAutoLabel();

}
