<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\devportal_repo_sync\Controller\RepoSyncViewController;
use Drupal\devportal_repo_sync\Exception\DevportalRepoSyncConnectionException;
use Drupal\devportal_repo_sync\Service\Client;

/**
 * Class RepoSyncUpdateForm.
 */
class RepoSyncUpdateForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devportal_repo_sync_update_form';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uuid = NULL) {
    $config = $this->config('devportal_repo_sync.config');
    $client = new Client($config->get('uuid'), hex2bin($config->get('secret')), $config->get('service'));

    try {
      $result = $client("GET", "/api/import/$uuid", NULL);

      if ($result[0] == 200) {
        $result = json_decode(array_pop($result), TRUE);
        $form['label'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Label'),
          '#description' => $this->t('A name for the import project.'),
          '#maxlength' => 64,
          '#size' => 64,
          '#default_value' => $result["Label"] ?? NULL,
          '#weight' => 1,
        ];
        $form['repository_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Repository URL'),
          '#description' => $this->t('The URL of the repository to import.'),
          '#maxlength' => 128,
          '#size' => 64,
          '#default_value' => $result["RepositoryURL"] ?? NULL,
          '#weight' => 3,
        ];
        $form['pattern'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Pattern'),
          '#description' => $this->t('A pattern to look for in the repository.'),
          '#maxlength' => 64,
          '#size' => 64,
          '#default_value' => $result["Pattern"] ?? NULL,
          '#weight' => 4,
        ];
        $form['reference'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Reference'),
          '#description' => $this->t('Branch reference'),
          '#maxlength' => 64,
          '#size' => 64,
          '#default_value' => $result["Reference"] ?? NULL,
          '#weight' => 5,
        ];
        $form['result'] = [
          '#type' => 'value',
          '#value' => $result ?? NULL,
        ];
        $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
          '#weight' => 7,
        ];
      }
      else {
        $form = RepoSyncViewController::error($result[1]);
      }
    }
    catch (DevportalRepoSyncConnectionException $e) {
      $this->messenger()->addError($e->getMessage());
      watchdog_exception('repo_sync', $e);
    }

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
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('devportal_repo_sync.controller_content');
    $config = $this->config('devportal_repo_sync.config');
    $result = $form_state->getValue('result');
    $result["Label"] = $form_state->getValue('label');
    $result["RepositoryURL"] = $form_state->getValue('repository_url');
    $result["Pattern"] = $form_state->getValue('pattern');
    $result["Reference"] = $form_state->getValue('reference');
    $client = new Client($config->get('uuid'), hex2bin($config->get('secret')), $config->get('service'));
    try {
      $client("PUT", "/api/import/{$result["ID"]}", json_encode($result));
    }
    catch (DevportalRepoSyncConnectionException $e) {
      $this->messenger()->addError($e->getMessage());
      watchdog_exception('repo_sync', $e);
      $form_state->setRedirect('devportal_repo_sync.update_form', [
        'uuid' => $result["ID"],
      ]);
    }
  }

}
