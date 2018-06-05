<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\devportal_repo_sync\Service\Client;

class RepoSyncDeleteForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this import?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    Url::fromRoute('devportal_repo_sync.repo_sync_controller_content');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devportal_repo_sync_delete_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $uuid = NULL) {
    $form_state->set('uuid_tmp', $uuid);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('devportal_repo_sync.repo_sync_controller_content');
    $uuid = $form_state->get('uuid_tmp');
    $config = $this->config('devportal_repo_sync.config');
    $client = new Client($config->get('uuid'), hex2bin($config->get('secret')), "http://service:8000");

    try {
      $client("DELETE", "/api/import/$uuid", NULL);
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
    }
  }

}
