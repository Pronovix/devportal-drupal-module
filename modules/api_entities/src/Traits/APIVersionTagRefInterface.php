<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;

interface APIVersionTagRefInterface {

  // @TODO Getters/setters(?)

  /**
   * Provides API Version Tag related base field definitions for an entity
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
  public static function apiVersionTagBaseFieldDefinitions(EntityTypeInterface $entity_type);

}
