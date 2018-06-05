<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\devportal_repo_sync\Service\Client;

/**
 * Class RepoSyncCreateForm.
 */
class RepoSyncCreateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devportal_repo_sync_create_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('A name for the import project.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => '',
      '#weight' => 1,
    ];
    $form['repository_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Repository URL'),
      '#description' => $this->t('The URL of the repository to import.'),
      '#maxlength' => 128,
      '#size' => 64,
      '#default_value' => '',
      '#weight' => 3,
    ];
    $form['pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pattern'),
      '#description' => $this->t('A pattern to look for in the repository.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => '',
      '#weight' => 4,
    ];
    $form['reference'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reference'),
      '#description' => $this->t('Branch reference'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => '',
      '#weight' => 5,
    ];
    $form['callback'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Callback'),
      '#description' => $this->t('Callback URL'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => '',
      '#weight' => 6,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#weight' => 7,
    ];

    return $form;
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
    $form_state->setRedirect('devportal_repo_sync.repo_sync_controller_content');
    $values = $form_state->getValues();
    $config = $this->config('devportal_repo_sync.config');
    $client = new Client($config->get('uuid'), hex2bin($config->get('secret')), "http://service:8000");

    try {
      $client("POST", "/api/import", json_encode([
        "Label" => $values["label"],
        "RepositoryType" => 'git',
        "RepositoryURL" => $values["repository_url"],
        "Pattern" => $values["pattern"],
        "Reference" => $values["reference"],
        "Callback" => $values["callback"],
        "Metadata" => [
          "foo" => "bar",
        ],
      ]));
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
    }
  }

}
