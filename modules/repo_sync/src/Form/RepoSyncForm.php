<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepoSyncForm.
 */
class RepoSyncForm extends FormBase {

  /**
   * Drupal\devportal_repo_sync\Service\RepoSyncConnector definition.
   *
   * @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector
   */
  protected $connection;

  /**
   * Constructs a new RepoSyncForm object.
   *
   * @param \Drupal\devportal_repo_sync\Service\RepoSyncConnector $connection
   *   A configured connection to the repository importer service.
   */
  public function __construct(RepoSyncConnector $connection) {
    $this->connection = $connection;
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
    return 'devportal_repo_sync_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uuid = NULL) {
    $result = $uuid ? $this->connection->getImport($uuid) : NULL;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('A name for the import project.'),
      '#default_value' => $result["Label"] ?? NULL,
      '#weight' => 1,
    ];

    $form['repository_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Repository URL'),
      '#description' => $this->t('The URL of the repository to import.'),
      '#default_value' => $result["RepositoryURL"] ?? NULL,
      '#weight' => 3,
    ];

    $form['pattern'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pattern'),
      '#description' => $this->t('A pattern to look for in the repository.'),
      '#default_value' => $result["Pattern"] ?? NULL,
      '#weight' => 4,
    ];

    $form['reference'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reference'),
      '#description' => $this->t('Branch reference'),
      '#default_value' => $result["Reference"] ?? NULL,
      '#weight' => 5,
    ];

    $form['base_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base path'),
      '#description' => $this->t('Path prefix for the imported content'),
      '#default_value' => $result["BasePath"] ?? NULL,
      '#weight' => 6,
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
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('devportal_repo_sync.controller_content');
    $values = $form_state->getValues();
    if (($result = $form_state->getValue('result'))) {
      $result["Label"] = $values['label'];
      $result["RepositoryURL"] = $values['repository_url'];
      $result["Pattern"] = $values['pattern'];
      $result["Reference"] = $values['reference'];
      $result["BasePath"] = $values['base_path'];
      $this->connection->updateImport($result);
    }
    else {
      $this->connection->createImport(
        $values["label"],
        $values["repository_url"],
        $values["pattern"],
        $values["reference"],
        $values["base_path"]
      );
    }
  }

}
