<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for consumes fields.
 */
trait ConsumesTrait {

  // @TODO Getters/setters(?)

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_entities\Traits\ConsumesInterface::consumesBaseFieldDefinitions()
   */
  public static function consumesBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['consumes'])) {
      $fields[$additional['api_extra_info']['consumes']] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Consumes'))
        ->setRevisionable(TRUE)
        ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
        ->setSettings([
          'target_type' => 'taxonomy_term',
          'handler' => 'default',
          'handler_settings' => [
            'target_bundles' => [
              'api_mime_type' => 'api_mime_type',
            ],
            'sort' => [
              'field' => '_none',
            ],
            'auto_create' => FALSE,
            'auto_create_bundle' => '',
          ],
        ])
        ->setDisplayOptions('form', [
          'type' => 'inline_entity_form_complex',
          'weight' => 5,
          'settings' => [
            'form_mode' => 'default',
            'override_labels' => TRUE,
            // @FIXME Should these use $this->>t()?
            'label_singular' => 'consumable',
            'label_plural' => 'consumable',
            'allow_new' => TRUE,
            'allow_existing' => TRUE,
            'match_operator' => 'CONTAINS',
          ],
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayOptions('view', [
          'type' => 'entity_reference_label',
          'weight' => 5,
          'settings' => [
            'link' => FALSE,
          ],
        ])
        ->setDisplayConfigurable('view', TRUE);
    }
    return $fields;
  }

}
