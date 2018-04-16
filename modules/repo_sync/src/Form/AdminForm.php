<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Global settings form.
 */
class AdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devportal_repo_sync_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('devportal_repo_sync.import');

    $form['webhook'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Webhooks'),
    ];

    $form['webhook']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable webhook integration'),
      '#default_value' => $config->get('webhook.enabled'),
    ];

    $form['webhook']['ping'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Notify external service'),
      '#description' => $this->t('Leave empty to disable'),
      '#default_value' => $config->get('webhook.ping'),
    ];

    $form['cache'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable caching'),
      '#default_value' => $config->get('cache'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('devportal_repo_sync.import')
      ->set('webhook.ping', $form_state->getValue('ping'))
      ->set('webhook.enabled', (bool) $form_state->getValue('enabled'))
      ->set('cache', (bool) $form_state->getValue('cache'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'devportal_repo_sync.import',
    ];
  }

}
