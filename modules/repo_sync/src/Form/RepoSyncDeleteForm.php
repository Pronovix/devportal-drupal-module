<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepoSyncDeleteForm.
 */
class RepoSyncDeleteForm extends ConfirmFormBase {

  /**
   * Drupal\devportal_repo_sync\Service\RepoSyncConnector definition.
   *
   * @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector
   */
  protected $devportalRepoSyncConnection;

  /**
   * Constructs a new RepoSyncDeleteForm object.
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
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this import?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('devportal_repo_sync.controller_content');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devportal_repo_sync_delete_form';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uuid = NULL) {
    $this->devportalRepoSyncConnection->getImport($uuid);
    $form['uuid'] = [
      '#type' => 'value',
      '#value' => $uuid,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('devportal_repo_sync.controller_content');
    $uuid = $form_state->getValue('uuid');
    $this->devportalRepoSyncConnection->deleteImport($uuid);
  }

}
