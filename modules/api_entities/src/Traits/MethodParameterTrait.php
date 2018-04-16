<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for method parameter fields.
 */
trait MethodParameterTrait {

  // @TODO Getters/setters(?)

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_entities\Traits\MethodParameterInterface::methodParameterBaseFieldDefinitions()
   */
  public static function methodParameterBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['method_parameter'])) {
      $key_method_parameter = $additional['api_extra_info']['method_parameter'];
      $fields[$key_method_parameter['name']] = BaseFieldDefinition::create('string')
        ->setLabel(t('Name'))
        ->setRequired(TRUE)
        ->setRevisionable(TRUE)
        ->setSetting('max_length', 255)
        ->setDisplayOptions('view', [
          'label' => 'hidden',
          'type' => 'string',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('view', TRUE)
        ->setDisplayOptions('form', [
          'type' => 'string_textfield',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('form', TRUE);

      $fields[$key_method_parameter['description']] = BaseFieldDefinition::create('text_long')
        ->setLabel(t('Description'))
        ->setRevisionable(TRUE)
        ->setTranslatable(TRUE)
        ->setDisplayOptions('view', [
          'label' => 'hidden',
          'type' => 'text_default',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('view', TRUE)
        ->setDisplayOptions('form', [
          'type' => 'text_textfield',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('form', TRUE);

      $fields[$key_method_parameter['required']] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Required'))
        ->setRevisionable(TRUE)
        ->setSettings([
          'on_label' => t('Yes'),
          'off_label' => t('No'),
        ])
        ->setDisplayOptions('view', [
          'label' => 'inline',
          'type' => 'boolean',
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
