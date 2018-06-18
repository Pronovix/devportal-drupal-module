<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepoSyncUpdateForm.
 */
class RepoSyncUpdateForm extends FormBase {

  /**
   * Drupal\devportal_repo_sync\Service\RepoSyncConnector definition.
   *
   * @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector
   */
  protected $devportalRepoSyncConnection;

  /**
   * Constructs a new RepoSyncUpdateForm object.
   *
   * @param \Drupal\devportal_repo_sync\Service\RepoSyncConnector $devportal_repo_sync_connection
   */
  public function __construct(RepoSyncConnector $devportal_repo_sync_connection) {
    $this->devportalRepoSyncConnection = $devportal_repo_sync_connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('devportal_repo_sync.connection')
    );
  }

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
    $result = $this->devportalRepoSyncConnection->getImport($uuid, TRUE);
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
    $result = $form_state->getValue('result');
    $result["Label"] = $form_state->getValue('label');
    $result["RepositoryURL"] = $form_state->getValue('repository_url');
    $result["Pattern"] = $form_state->getValue('pattern');
    $result["Reference"] = $form_state->getValue('reference');
    $this->devportalRepoSyncConnection->updateImport($result);
  }

}
