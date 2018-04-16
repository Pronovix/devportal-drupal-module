<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for the API Version Tag field.
 */
trait APIVersionTagRefTrait {

  // @TODO Getters/setters(?)

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface::apiVersionTagBaseFieldDefinitions()
   */
  public static function apiVersionTagBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['api_version_tag'])) {
      $fields[$additional['api_extra_info']['api_version_tag']] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('API Version Tag'))
        ->setRequired(TRUE)
        ->setRevisionable(TRUE)
        ->setSettings([
          'target_type' => 'api_version_tag',
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
            'label_singular' => 'API Version Tag',
            'label_plural' => 'API Version Tags',
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
