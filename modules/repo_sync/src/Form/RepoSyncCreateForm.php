<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\devportal_repo_sync\Service\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepoSyncCreateForm.
 */
class RepoSyncCreateForm extends FormBase {

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new RepoSyncController object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
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
      '#default_value' => $this->t('Test import'),
      '#weight' => 1,
    ];
    $form['repository_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Repository Type'),
      '#description' => $this->t('Select the repository type.'),
      '#options' => ['git' => $this->t('Git')],
      '#size' => 1,
      '#default_value' => 'git',
      '#weight' => 2,
    ];
    $form['repository_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Repository URL'),
      '#description' => $this->t('The URL of the repository to import.'),
      '#maxlength' => 128,
      '#size' => 64,
      '#default_value' => 'https://github.com/tamasd/git-test.git',
      '#weight' => 3,
    ];
    $form['pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pattern'),
      '#description' => $this->t('A pattern to look for in the repository.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => 'docs/',
      '#weight' => 4,
    ];
    $form['reference'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reference'),
      '#description' => $this->t('Branch reference'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => 'refs/heads/master',
      '#weight' => 5,
    ];
    $form['callback'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Callback'),
      '#description' => $this->t('Callback URL'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => 'http://storage:8888',
      '#weight' => 6,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
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
    $values = $form_state->getValues();
    $config = $this->config('devportal_repo_sync.config');
    $client = new Client($config->get('uuid'), hex2bin($config->get('secret')), "http://service:8000");

    try {
      $client("POST", "/api/import", json_encode([
        "Label" => $values["label"],
        "RepositoryType" => $values["repository_type"],
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
