<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for the API External Documentation field.
 */
trait APIExtDocRefTrait {

  // @TODO Getters/setters(?)

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_entities\Traits\APIExtDocInterface::apiExtDocBaseFieldDefinitions()
   */
  public static function apiExtDocBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['api_ext_doc'])) {
      $fields[$additional['api_extra_info']['api_ext_doc']] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('API External Documentation'))
        ->setRevisionable(TRUE)
        ->setSettings([
          'target_type' => 'api_ext_doc',
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
            'label_singular' => 'API External Documentation',
            'label_plural' => 'API External Documentations',
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
