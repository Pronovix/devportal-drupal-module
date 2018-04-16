<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for vendor extensions.
 */
trait VendorExtensionTrait {

  // @TODO Getters/setters(?)

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_entities\Traits\VendorExtensionInterface::vendorExtensionBaseFieldDefinitions()
   */
  public static function vendorExtensionBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['vendor_extension'])) {
      $fields[$additional['api_extra_info']['vendor_extension']] = BaseFieldDefinition::create('vendor_extension')
        ->setLabel(t('Extensions'))
        ->setRevisionable(TRUE)
        ->setTranslatable(TRUE)
        ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
        ->setDisplayOptions('form', [
          'type' => 'vendor_extension_default',
          'weight' => 5,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayOptions('view', [
          'type' => 'vendor_extension_default',
          'weight' => 5,
        ])
        ->setDisplayConfigurable('view', TRUE);
    }
    return $fields;
  }

}
