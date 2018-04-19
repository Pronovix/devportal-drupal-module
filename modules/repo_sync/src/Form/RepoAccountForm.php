<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\migrate\Plugin\Migration;
use Drupal\devportal_repo_sync\Entity\RepoAccount;
use Drupal\devportal_repo_sync\Plugin\migrate\source\RepositorySource;
use Drupal\devportal_repo_sync\RepoSourceInfoTrait;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add/edit form for the RepoAccount entity.
 */
class RepoAccountForm extends EntityForm {

  use ContainerAwareTrait;
  use RepoSourceInfoTrait;

  /**
   * @var QueryFactory
   */
  protected $entityQuery;

  /**
   * @var ConfigFactory
   */
  protected $config;

  /**
   * Only the default / selected method will be shown in the user interface.
   *
   * @var bool
   */
  protected $restrict_methods;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var QueryFactory $entity_query */
    $entity_query = $container->get('entity.query');

    /** @var MigrateSourcePluginManager $source_plugin_manager */
    $source_plugin_manager = $container->get('plugin.manager.migrate.source');

    /** @var ConfigFactory $configFactory */
    $configFactory = $container->get('config.factory');

    return new static(
      $container,
      $entity_query,
      $source_plugin_manager,
      $configFactory
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ContainerInterface $container, QueryFactory $entityQuery, MigrateSourcePluginManager $migrateSourcePluginManager, ConfigFactory $configFactory) {
    $this->entityQuery = $entityQuery;
    $this->migrateSourcePluginManager = $migrateSourcePluginManager;
    $this->config = $configFactory;
    $this->setContainer($container);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $this->applyConfigDefaults($form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->getEntity()->getLabel(),
      '#required' => TRUE,
    ];

    $repositoryProviders = $this->getRepositoryProviders();
    $default_provider = $form_state->getValue('provider') ?: $this->getEntity()->getProvider();
    $form['provider'] = [
      '#type' => 'radios',
      '#title' => $this->t('Repository provider'),
      '#default_value' => $default_provider,
      '#options' => $repositoryProviders,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::providerSelected',
        'event' => 'change',
        'wrapper' => $this->getEntity()->isNew() ? 'repo-account-add-form' : 'repo-account-edit-form',
      ],
    ];

    $methods = $this->getAuthenticationMethods($form_state);
    $provided_method = $form_state->getValue('method') ?: $this->getEntity()->getMethod();
    $default_value = empty($methods[$provided_method]) ? NULL : $provided_method;
    if ($this->restrict_methods && $default_value) {
      $methods = [$default_value => $methods[$default_value]];
    }

    $form['method'] = [
      '#type' => 'radios',
      '#title' => $this->t('Authentication method'),
      '#default_value' => $default_value,
      '#options' => $methods,
      '#required' => TRUE,
      '#description' => $this->t('This authentication method can be used in place of your original password (read <a href=":auth-info-link">more</a>).', [
        ':auth-info-link' => $this->config->get('devportal_repo_sync.import')->get("help.auth.{$default_provider}"),
      ]),
    ];

    $form['identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifier'),
      '#default_value' => $this->getEntity()->getIdentifier(),
      '#required' => TRUE,
      '#description' => $this->t('Your @provider username.', [
        '@provider' => $repositoryProviders[$default_provider],
      ]),
    ];

    $form['secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret'),
      '#default_value' => $this->getEntity()->getSecret(),
      '#required' => TRUE,
      '#description' => $this->t('Your generated personal access token.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    if (isset($actions['delete'])) {
      $actions['delete']['#attributes']['class'][] = 'btn';
      $actions['delete']['#attributes']['class'][] = 'btn-danger';
    }

    return $actions;
  }

  /**
   * Applies default values from the site config on the form state.
   *
   * @param FormStateInterface $form_state
   */
  protected function applyConfigDefaults(FormStateInterface $form_state) {
    $this->restrict_methods = (bool) $this->config->get('devportal_repo_sync.import')->get('account.restrict_methods');

    if (!$this->getEntity()->isNew()) {
      return;
    }

    $default_provider = $this->config->get('devportal_repo_sync.import')->get('account.default_provider');
    if ($default_provider && !$form_state->getValue('provider')) {
      $form_state->setValue('provider', $default_provider);
    }

    $default_method = $this->config->get('devportal_repo_sync.import')->get('account.default_method');
    if ($default_method && !$form_state->getValue('method')) {
      $form_state->setValue('method', $default_method);
    }
  }

  /**
   * AJAX callback when a certain provider is selected on the form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function providerSelected(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if ($form_state->isRebuilding()) {
      return;
    }

    if ($form_state->getTriggeringElement()['#type'] !== 'submit') {
      return;
    }

    /** @var RepositorySource $plugin */
    $plugin = $this->migrateSourcePluginManager->createInstance($this->getEntity()->getProvider(), [
      'method' => $this->getEntity()->getMethod(),
      'identifier' => $this->getEntity()->getIdentifier(),
      'secret' => $this->getEntity()->getSecret(),
    ], Migration::create($this->container, [], '', []));

    try {
      $plugin->authenticate(TRUE);
    }
    catch (\Exception $ex) {
      $providers = $this->getRepositoryProviders();
      $provider = $providers[$this->getEntity()->getProvider()];
      $form_state->setError($form, $this->t('Authentication failed. Response from @provider: %message', [
        '%message' => $ex->getMessage(),
        '@provider' => $provider,
      ]));
    }
  }

  /**
   * Helper function that returns the authentication methods for a provider.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  private function getAuthenticationMethods(FormStateInterface $form_state) {
    $provider = $form_state->getValue('provider') ?: $this->getEntity()->getProvider();
    if ($provider) {
      $definition = $this->migrateSourcePluginManager->getDefinition($provider);
      return $definition['methods'];
    }

    return [];
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->getEntity()->save();

    $params = [
      '%label' => $this->getEntity()->getLabel(),
    ];

    drupal_set_message($status ?
      $this->t('Saved the %label repository account.', $params) :
      $this->t('The %label repository account was not saved.', $params)
    );

    $form_state->setRedirect('entity.repo_account.collection');
  }

  /**
   * Checks if a repository account exists.
   *
   * @param string $uuid
   * @return bool
   */
  public function exists($uuid) {
    $entity = $this->entityQuery->get('repo_account')
      ->condition('uuid', $uuid)
      ->execute();

    return (bool) $entity;
  }

  /**
   * {@inheritdoc}
   *
   * @return RepoAccount
   */
  public function getEntity() {
    /** @var RepoAccount $entity */
    $entity = parent::getEntity();

    return $entity;
  }

}
