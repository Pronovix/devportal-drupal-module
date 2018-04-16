<?php

namespace Drupal\devportal_api_entities\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides a trait for item fields.
 */
trait ItemTrait {

  // @TODO Getters/setters(?)

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_entities\Traits\ItemInterface::itemBaseFieldDefinitions()
   *
   * @param $allow_file bool
   *   TRUE for allowing file items. Defaults to FALSE.
   */
  public static function itemBaseFieldDefinitions(EntityTypeInterface $entity_type, $allow_file = FALSE) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['item'])) {
      $key_item = $additional['api_extra_info']['item'];
      $allowed_values = [
        'string' => 'string',
        'number' => 'number',
        'integer' => 'integer',
        'boolean' => 'boolean',
        'array' => 'array',
      ];
      if ($allow_file) {
        $allowed_values['file'] = 'file';
      }
      $fields[$key_item['type']] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('Type'))
        ->setRequired(TRUE)
        ->setRevisionable(TRUE)
        ->setSettings([
          'allowed_values' => $allowed_values
        ])
        ->setDisplayOptions('view', [
          'label' => 'hidden',
          'type' => 'string',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('view', TRUE)
        ->setDisplayOptions('form', [
          'type' => 'options_select',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('form', TRUE);

      $fields[$key_item['format']] = BaseFieldDefinition::create('string')
        ->setLabel(t('Format'))
        ->setTranslatable(TRUE)
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

      $fields[$key_item['items']] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('API Parameter Items'))
        ->setRevisionable(TRUE)
        ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
        ->setSettings([
          'target_type' => 'api_param_item',
          'handler' => 'default',
          'handler_settings' => [
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
            'label_singular' => 'API Parameter Item',
            'label_plural' => 'API Parameter Items',
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

      $fields[$key_item['collection_format']] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('CollectionFormat'))
        ->setRevisionable(TRUE)
        ->setSettings([
          'allowed_values' => [
            'csv' => 'comma separated values',
            'ssv' => 'space separated values',
            'tsv' => 'tab separated values',
            'pipes' => 'pipe separated values',
            'multi' => 'corresponds to multiple parameter instances instead of multiple values for a single instance',
          ]
        ])
        ->setDisplayOptions('view', [
          'label' => 'hidden',
          'type' => 'string',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('view', TRUE)
        ->setDisplayOptions('form', [
          'type' => 'options_select',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('form', TRUE);

      // @TODO Serialized JSON blob.
      $fields[$key_item['default']] = BaseFieldDefinition::create('string_long')
        ->setLabel(t('Default'))
        ->setRevisionable(TRUE)
        // @FIXME Does it need to be displayed, eg. via a special field formatter?
        ->setDisplayOptions('view', [
          'label' => 'hidden',
          'type' => 'text_default',
          'weight' => 0,
        ])
        ->setDisplayConfigurable('view', TRUE)
        // @FIXME: Does it need any form widget at all? If not, set to read-only.
        ->setDisplayOptions('form', [
          'type' => 'text_textfield',
          'weight' => 0,
        ])
        ->setDisplayConfigurable('form', TRUE);

    }

    return $fields;
  }

}
