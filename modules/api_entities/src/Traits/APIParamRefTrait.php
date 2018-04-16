<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for the API Parameter field.
 */
trait APIParamRefTrait {

  // @TODO Getters/setters(?)

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_entities\Traits\APIParamRefInterface::apiParamBaseFieldDefinitions()
   */
  public static function apiParamBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['api_param'])) {
      $fields[$additional['api_extra_info']['api_param']] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('API Parameter'))
        ->setRevisionable(TRUE)
        ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
        ->setSettings([
          'target_type' => 'api_param',
          'handler' => 'default',
          'handler_settings' => [
            'sort' => [
              'field' => '_none',
            ],
            'auto_create' => FALSE,
            'auto_create_bundle' => '',
          ],
        ])
        ->setDisplayOptions('view', [
          'label' => 'hidden',
          'type' => 'entity_reference_label',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('view', TRUE)
        ->setDisplayOptions('form', [
          'type' => 'inline_entity_form_complex',
          'weight' => 5,
          'settings' => [
            'form_mode' => 'default',
            'override_labels' => TRUE,
            // @FIXME Should these use $this->>t()?
            'label_singular' => 'API Parameter',
            'label_plural' => 'API Parameters',
            'allow_new' => TRUE,
            'allow_existing' => TRUE,
            'match_operator' => 'CONTAINS',
          ],
        ])
        ->setDisplayConfigurable('form', TRUE);
    }
    return $fields;
  }

}
