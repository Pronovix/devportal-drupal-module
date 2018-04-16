<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for allow empty value fields.
 */
trait AllowEmptyValueTrait {

  // @TODO Getters/setters(?)

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_entities\Traits\AllowEmptyValueInterface::allowEmptyValueBaseFieldDefinitions()
   */
  public static function allowEmptyValueBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['allow_empty_value'])) {
      $fields[$additional['api_extra_info']['allow_empty_value']] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Allow empty value'))
        ->setRevisionable(TRUE)
        ->setSettings([
          'on_label' => t('Yes'),
          'off_label' => t('No'),
        ])
        ->setDisplayOptions('view', [
          'label' => 'inline',
          'type' => 'boolean',
          'settings' => [
            'format' => 'default',
          ],
          'weight' => -5,
        ])
        ->setDisplayConfigurable('view', TRUE)
        ->setDisplayOptions('form', [
          'type' => 'boolean_checkbox',
          'settings' => [
            'display_label' => TRUE,
          ],
          'weight' => -5,
        ])
        ->setDisplayConfigurable('form', TRUE);

    }
    return $fields;
  }

}
