<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Entity\ContentLanguageSettings;

/**
 * Class APIGlobalSchemaTypeForm.
 *
 * @package Drupal\devportal_api_entities\Form
 */
class APIGlobalSchemaTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $api_global_schema_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $api_global_schema_type->label(),
      '#description' => $this->t("Label for the API Global Schema type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $api_global_schema_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\devportal_api_entities\Entity\APIGlobalSchemaType::load',
      ],
      '#disabled' => !$api_global_schema_type->isNew(),
    ];

    // $form['langcode'] is not wrapped in an
    // if ($this->moduleHandler->moduleExists('language')) check because the
    // language_select form element works also without the language module being
    // installed. https://www.drupal.org/node/1749954 documents the new element.
    $form['langcode'] = [
      '#type' => 'language_select',
      '#title' => $this->t('API Global Schema type language'),
      '#languages' => LanguageInterface::STATE_ALL,
      '#default_value' => $api_global_schema_type->language()->getId(),
    ];
    if ($this->moduleHandler->moduleExists('language')) {
      $form['default_terms_language'] = [
        '#type' => 'details',
        '#title' => $this->t('API Global Schemas language'),
        '#open' => TRUE,
      ];
      $form['default_terms_language']['default_language'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'api_global_schema',
          'bundle' => $api_global_schema_type->id(),
        ],
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('api_global_schema', $api_global_schema_type->id()),
      ];
    }

    switch ($this->operation) {
      case 'edit':
        $form['#title'] = $this->t('Edit API Global Schema type %label', ['%label' => $api_global_schema_type->label()]);
        break;

      case 'add':
        $form['#title'] = $this->t('Add API Global Schema type');
        break;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $api_global_schema_type = $this->entity;
    $status = $api_global_schema_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label API Global Schema type.', [
          '%label' => $api_global_schema_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label API Global Schema type.', [
          '%label' => $api_global_schema_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($api_global_schema_type->toUrl('collection'));
  }

}

