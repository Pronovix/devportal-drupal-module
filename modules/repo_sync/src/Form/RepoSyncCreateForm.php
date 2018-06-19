<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepoSyncCreateForm.
 */
class RepoSyncCreateForm extends FormBase {

  /**
   * Drupal\devportal_repo_sync\Service\RepoSyncConnector definition.
   *
   * @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector
   */
  protected $devportalRepoSyncConnection;

  /**
   * Constructs a new RepoSyncCreateForm object.
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
    $values = $form_state->getValues();
    $this->devportalRepoSyncConnection->createImport(
      $values["label"],
      $values["repository_url"],
      $values["pattern"],
      $values["reference"]
    );
  }

}
