<?php

namespace Drupal\devportal_api_reference\Traits;

use Drupal\Core\Entity\EntityTypeInterface;

interface APIRefRefInterface {

  // @TODO Getters/setters(?)

  /**
   * Provides API Reference entity reference field definition for an entity
   * type.
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
  public static function apiRefBaseFieldDefinitions(EntityTypeInterface $entity_type);

}
