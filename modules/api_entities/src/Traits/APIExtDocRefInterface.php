<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;

interface APIExtDocRefInterface {

  // @TODO Getters/setters(?)

  /**
   * Provides API External Documentation related base field definitions for an
   * entity type.
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
  public static function apiExtDocBaseFieldDefinitions(EntityTypeInterface $entity_type);

}
