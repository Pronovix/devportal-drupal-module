<?php

namespace Drupal\devportal_api_entities\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'vendor_extensions' field type.
 *
 * @FieldType(
 *   id = "vendor_extension",
 *   label = @Translation("Vendor extension"),
 *   description = @Translation("This field stores a name and a serialized value."),
 *   category = @Translation("DP Docs"),
 *   default_widget = "vendor_extension_default",
 *   default_formatter = "vendor_extension_default",
 *   constraints = {
 *     "VendorExtensionRequiredParts" = {},
 *   }
 * )
 */
class VendorExtension extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'name' => [
          'description' => 'Name.',
          'type' => 'varchar',
          'length' => '255',
          'not null' => TRUE,
          'default' => '',
        ],
        'value' => [
          'description' => 'Value.',
          'type' => 'blob',
          'size' => 'big',
          'not null' => TRUE,
        ],
      ],
      'indexes' => [
        'name' => ['name'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['name'] = DataDefinition::create('string')
      ->setLabel(t('Name'));
    $properties['value'] = DataDefinition::create('any')
      ->setLabel(t('Value'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if (empty($this->values['name']) && empty($this->values['value'])) {
      return TRUE;
    }
    return parent::isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    $manager = \Drupal::typedDataManager()->getValidationConstraintManager();

    $constraints[] = $manager->create('VendorExtensionRequiredParts', []);

    return $constraints;
  }

}
