<?php

namespace Drupal\devportal_api_reference;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Entity\ContentLanguageSettings;

/**
 * Class APIRefTypeForm.
 *
 * @package Drupal\devportal_api_reference\Form
 */
class APIRefTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $api_ref_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $api_ref_type->label(),
      '#description' => $this->t("Label for the API Reference type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $api_ref_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\devportal_api_reference\Entity\APIRefType::load',
      ],
      '#disabled' => !$api_ref_type->isNew(),
    ];

    // $form['langcode'] is not wrapped in an
    // if ($this->moduleHandler->moduleExists('language')) check because the
    // language_select form element works also without the language module being
    // installed. https://www.drupal.org/node/1749954 documents the new element.
    $form['langcode'] = [
      '#type' => 'language_select',
      '#title' => $this->t('API Reference type language'),
      '#languages' => LanguageInterface::STATE_ALL,
      '#default_value' => $api_ref_type->language()->getId(),
    ];
    if ($this->moduleHandler->moduleExists('language')) {
      $form['default_terms_language'] = [
        '#type' => 'details',
        '#title' => $this->t('API References language'),
        '#open' => TRUE,
      ];
      $form['default_terms_language']['default_language'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'api_ref',
          'bundle' => $api_ref_type->id(),
        ],
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('api_ref', $api_ref_type->id()),
      ];
    }

    switch ($this->operation) {
      case 'edit':
        $form['#title'] = $this->t('Edit API Reference type %label', ['%label' => $api_ref_type->label()]);
        break;

      case 'add':
        $form['#title'] = $this->t('Add API Reference type');
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
    $api_ref_type = $this->entity;
    $status = $api_ref_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label API Reference type.', [
          '%label' => $api_ref_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label API Reference type.', [
          '%label' => $api_ref_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($api_ref_type->toUrl('collection'));
  }

}

