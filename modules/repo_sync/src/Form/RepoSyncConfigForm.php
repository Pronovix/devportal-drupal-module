<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The config form for the UUID and secret key.
 */
class RepoSyncConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'devportal_repo_sync.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devportal_repo_sync_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('devportal_repo_sync.config');
    $form['account'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account ID'),
      '#description' => $this->t('The UUID of your account'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('account'),
      '#required' => TRUE,
    ];
    $form['secret'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Secret'),
      '#description' => $this->t('The secret key of your account'),
      '#default_value' => $config->get('secret'),
      '#required' => TRUE,
    ];
    $form['service'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service URL'),
      '#description' => $this->t('The URL of the server you wish to use'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('service'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('devportal_repo_sync.config')
      ->set('account', $form_state->getValue('account'))
      ->set('secret', $form_state->getValue('secret'))
      ->set('service', $form_state->getValue('service'))
      ->save();
  }

}
