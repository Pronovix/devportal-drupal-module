<?php

namespace Drupal\devportal_api_reference\Traits;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\devportal_api_reference\Entity\APIRef;

/**
 * Provides a trait for the API Reference entity reference field.
 */
trait APIRefTrait {

  // @TODO Getters/setters(?)

  /**
   * Gets the API Reference field value.
   *
   * @return mixed
   */
  public function getAPIRef() {
    $additional = $this->getEntityType()->get('additional');
    return $this->get($additional['api_extra_info']['api_ref'])->getValue();
  }

  /**
   * Gets the API Reference entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null|static
   */
  public function getAPIRefEntity() {
    return APIRef::load($this->getAPIRef()[0]['target_id']);
  }

  /**
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   * @see \Drupal\devportal_api_reference\Traits\APIRefRefInterface::apiRefBaseFieldDefinitions()
   */
  public static function apiRefBaseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $additional = $entity_type->get('additional');
    if (isset($additional['api_extra_info']['api_ref'])) {
      $fields[$additional['api_extra_info']['api_ref']] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('API Reference'))
        ->setRequired(TRUE)
        // DO NOT make this field revisionable, it is not necessary. During its
        // "life" an API Docs entity can only belong to one API Reference
        // entity.
        ->setSettings([
          'target_type' => 'api_ref',
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
          'region' => 'hidden',
        ])
        ->setDisplayConfigurable('view', TRUE)
        ->setDisplayOptions('form', [
          'type' => 'inline_entity_form_complex',
          'weight' => 5,
          'settings' => [
            'form_mode' => 'default',
            'override_labels' => TRUE,
            // @FIXME Should these use $this->>t()?
            'label_singular' => 'API Reference',
            'label_plural' => 'API References',
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
