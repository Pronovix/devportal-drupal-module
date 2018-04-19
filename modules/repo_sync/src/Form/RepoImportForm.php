<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\book\BookManager;
use Drupal\book\BookManagerInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\devportal_api_reference\Entity\APIRefType;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\migrate\Plugin\Migration;
use Drupal\devportal_migrate_batch\Batch\MigrateBatch;
use Drupal\devportal_repo_sync\Entity\RepoAccount;
use Drupal\devportal_repo_sync\Entity\RepoImport;
use Drupal\devportal_repo_sync\FakeBookNode;
use Drupal\devportal_repo_sync\Plugin\migrate\source\RepositorySource;
use Drupal\devportal_repo_sync\RepoSourceInfoTrait;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add/edit form for the RepoImport entity.
 */
class RepoImportForm extends EntityForm {

  use ContainerAwareTrait;
  use RepoSourceInfoTrait;

  /**
   * List of steps in the multistep form.
   */
  const STEPS = [
    'selectAccount' => 'Account',
    'selectRepository' => 'Repository',
    'selectFiles' => 'Files',
  ];

  /**
   * @var MigratePluginManager
   */
  protected $migrateProcessPluginManager;

  /**
   * @var BookManagerInterface
   */
  protected $bookManager;

  /**
   * @var FakeBookNode
   */
  protected $fakeNode;

  /**
   * @var array
   */
  protected $processPlugins;

  /**
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * @var ConfigFactory
   */
  protected $config;

  /**
   * @var UuidInterface
   */
  protected $uuid;

  /**
   * AJAX settings for all checkboxes.
   *
   * @var array
   */
  private $checkboxAjax = [
    '#ajax' => [
      'callback' => '::ajaxCallback',
      'wrapper' => 'repo-import-form-wrapper',
      'event' => 'change',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(ContainerInterface $container, ConfigFactory $configFactory, AccountInterface $currentUser, MigrateSourcePluginManager $migrateSourcePluginManager, MigratePluginManager $migrateProcessPluginManager, BookManagerInterface $bookManager, UuidInterface $uuid) {
    $this->setContainer($container);
    $this->config = $configFactory;
    $this->currentUser = $currentUser;
    $this->migrateSourcePluginManager = $migrateSourcePluginManager;
    $this->migrateProcessPluginManager = $migrateProcessPluginManager;
    $this->bookManager = $bookManager;
    $this->uuid = $uuid;
    $this->fakeNode = new FakeBookNode([], 'node');
    $this->fakeNode->book = $this->bookManager->getLinkDefaults('new');
    $this->fakeNode->book['parent_depth_limit'] = BookManager::BOOK_MAX_DEPTH - 1;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var MigrateSourcePluginManager $source_plugin_manager */
    $source_plugin_manager = $container->get('plugin.manager.migrate.source');

    /** @var MigratePluginManager $process_plugin_manager */
    $process_plugin_manager = $container->get('plugin.manager.migrate.process');

    /** @var BookManager $bookManager */
    $bookManager = $container->get('book.manager');

    /** @var AccountInterface $currentUser */
    $currentUser = $container->get('current_user');

    /** @var ConfigFactory $config */
    $config = $container->get('config.factory');

    /** @var UuidInterface $uuid_service */
    $uuid_service = $container->get('uuid');

    return new static($container, $config, $currentUser, $source_plugin_manager, $process_plugin_manager, $bookManager, $uuid_service);
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $config
   *
   * @return \Drupal\devportal_repo_sync\Plugin\migrate\source\RepositorySource
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function createPlugin(FormStateInterface $form_state, $config = []) {
    /** @var RepoAccount $repo_account */
    $repo_account = RepoAccount::load($form_state->getValue('repo_account_id'));
    /** @var RepositorySource $plugin */
    $plugin = $this->migrateSourcePluginManager->createInstance($repo_account->getProvider(), $config + [
      'method' => $repo_account->getMethod(),
      'identifier' => $repo_account->getIdentifier(),
      'secret' => $repo_account->getSecret(),
    ], Migration::create($this->container, [], '', []));

    $plugin->authenticate();

    return $plugin;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['#prefix'] = '<div id="repo-import-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['#attached']['library'][] = 'devportal_repo_sync/repo-content-import-form';

    $fields = ['uuid', 'repo_account_id', 'repository', 'version', 'label'];

    $entity = $this->getEntity();

    if ($entity->isNew()) {
      $entity->uuid = $entity->uuid ?: $this->uuid->generate();
      if (static::isPublicAddress()) {
        $entity->generateWebhook();
      }
    }

    foreach ($fields as $field) {
      $val = $form_state->getValue($field) ?: $entity->{$field};
      $form_state->setValue($field, $val);

      $form[$field] = [
        '#type' => 'value',
        '#value' => $val,
      ];
    }

    if ($this->entity->isNew()) {
      $step = $this->getStep($form_state);
      $form['step'] = [
        '#type' => 'value',
        '#default_value' => $step,
      ];

      $this->stepWidget($form, $form_state);

      $form = $this->dispatch('form', $form, $form_state);
    }
    else {
      $form = $this->selectFilesStepForm($form, $form_state, TRUE);
    }

    return $form;
  }

  /**
   * Calls a method for the current step.
   *
   * @param $method
   *   Can be 'form', 'validate', 'submit'.
   * @param array $form
   *   Form structure.
   * @param FormStateInterface $form_state
   *
   * @return mixed
   */
  protected function dispatch($method, array &$form, FormStateInterface $form_state) {
    $step = $this->getStep($form_state);
    $method = ucfirst($method);
    return call_user_func_array([$this, "{$step}Step{$method}"], [
      &$form, $form_state,
    ]);
  }

  /**
   * Returns the current step from the form state.
   *
   * @param FormStateInterface $form_state
   *
   * @return mixed
   */
  protected function getStep(FormStateInterface $form_state) {
    $validSteps = array_keys(static::STEPS);

    $step = $form_state->getValue('step');
    return in_array($step, $validSteps) ? $step : reset($validSteps);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->entity->isNew()) {
      $this->dispatch('validate', $form, $form_state);
    }
    else {
      $this->selectFilesStepValidate($form, $form_state);
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->entity->isNew()) {
      $this->dispatch('submit', $form, $form_state);
    }
    else {
      $this->selectFilesStepSubmit($form, $form_state);
      $form_state->setRedirect('entity.repo_import.canonical', [
        'repo_import' => $this->entity->id(),
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    if ($this->entity->isNew()) {
      $step = $this->getStep($form_state);
      switch ($step) {
        case 'selectRepository':
        case 'selectAccount':
          $actions['submit']['#value'] = $this->t('Next');
          break;

        case 'selectFiles':
          $actions['import'] = [
            '#value' => $this->t('Save & Import'),
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#submit' => ['::submitForm', '::submitStartImport'],
          ];
          break;
      }
      $actions['submit']['#submit'] = ['::submitForm'];
    }
    return $actions;
  }

  /**
   * Form builder for the account selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @return array
   */
  protected function selectAccountStepForm(array $form, FormStateInterface $form_state) {
    $accountList = [];
    foreach (RepoAccount::loadMultiple() as $repo_account) {
      /** @var RepoAccount $repo_account */
      $accountList[$repo_account->id()] = $repo_account->label();
    }

    $form['repo_account_id'] = [
      '#title' => $this->t('Select an account'),
      '#type' => 'radios',
      '#options' => $accountList + ['new' => $this->t('Create new account')],
      '#required' => TRUE,
    ] + $this->checkboxAjax;

    if ($form_state->getValue('repo_account_id') === 'new') {
      $accountForm = $this->createRepoAccountForm($form_state);
      $form['newaccount'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Create new account'),
        '#collapsible' => FALSE,
        '#tree' => TRUE,
      ] + $accountForm->form([], $form_state);
      unset($form['newaccount']['provider']['#ajax']);
      $form['newaccount']['provider'] += $this->checkboxAjax;
    }

    return $form;
  }

  /**
   * Validation callback for the account selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function selectAccountStepValidate(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('repo_account_id') === 'new' && isset($form['newaccount'])) {
      $accountForm = $this->createRepoAccountForm($form_state);
      $rebuilding = $form_state->isRebuilding();
      $form_state->setRebuild(FALSE);
      $accountForm->validateForm($form['newaccount'], $form_state);
      $form_state->setRebuild($rebuilding);
    }
  }

  /**
   * Submit handler for the account selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function selectAccountStepSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    if ($form_state->getValue('repo_account_id') === 'new') {
      $accountForm = $this->createRepoAccountForm($form_state);
      $repoAccount = $accountForm->getEntity();
      $repoAccount->save();
      $form_state->setValue('repo_account_id', $repoAccount->id());
    }
    $form_state->setValue('step', 'selectRepository');
  }

  /**
   * Form builder for the repository selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function selectRepositoryStepForm(array $form, FormStateInterface $form_state) {
    $plugin = $this->createPlugin($form_state);
    $repositories = $this->getRepositories($plugin, $form_state);

    $form['repositories'] = [
      '#type' => 'value',
      '#value' => $repositories,
    ];

    $form['public'] = [
      '#type' => 'container',
      'label' => [
        '#type' => 'label',
        '#title' => $this->t('Add public repository'),
      ],
      'reponame' => [
        '#type' => 'textfield',
        '#title' => $this->t('Repository name'),
        '#element_validate' => ['::reponameValidate'],
      ],
      'add' => [
        '#type' => 'submit',
        '#value' => $this->t('Add to list'),
        '#skip_version_validation' => TRUE,
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => 'repo-import-form-wrapper',
          'event' => 'click',
        ],
        '#submit' => ['::addRepo'],
      ],
    ];

    $form['repositorylist'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['repository-list'],
      ],
      'repositorylist_label' => [
        '#title_display' => 'before',
        '#type' => 'label',
        '#title' => $this->t('Select repository'),
      ],
    ];

    foreach ($repositories as $repo) {
      $item = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['repository'],
        ],
      ];

      $plugin->setRepository($repo);

      $branchesAndTags = $this->getBranchesAndTagsFor($plugin, $form_state, $repo, TRUE);
      $existings_versions = $this->existingImportedVersions($repo);

      $item['expand'] = [
        '#type' => 'submit',
        '#submit' => ['::expandRepository'],
        '#value' => $repo,
        '#full_name' => $repo,
        '#disabled' => (bool) $branchesAndTags,
        '#skip_version_validation' => TRUE,
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => 'repo-import-form-wrapper',
          'event' => 'click',
        ],
      ];

      if ($branchesAndTags) {
        $options = [];
        $already_existing = [];
        foreach (['branches', 'tags'] as $version_type) {
          if ($branchesAndTags[$version_type]) {
            foreach ($branchesAndTags[$version_type] as $version) {
              if (in_array($version, $existings_versions)) {
                $already_existing[$version] = $version;
              }
              else {
                $options[$version] = $version;
              }
            }
          }
        }

        if (count($options) > 0 || count($already_existing) > 0) {
          $title = $this->t('Branches and tags');
          if (count($options) > 0) {
            $item['version_item'] = [
              '#type' => 'radios',
              '#options' => $options,
              '#after' => $already_existing ? $this->t('Already imported: %list', [
                '%list' => implode(', ', $already_existing),
              ]) : '',
              '#title' => $title,
              '#attributes' => [
                'class' => ['branches-and-tags'],
              ],
            ];
          }
          if (count($already_existing) > 0) {
            $item['version_item_already_imported'] = [
              '#type' => 'radios',
              '#options' => $already_existing,
              '#disabled' => TRUE,
              '#title' => $title,
              '#title_display' => count($options) > 0 ? 'invisible' : 'before',
              '#attributes' => [
                'class' => ['branches-and-tags', 'already-imported'],
                'title' => $this->t('Already imported'),
              ],
            ];
          }
        }
        else {
          $item['no_versions'] = [
            '#markup' => '<p>' . $this->t('This repository has no tags or branches') . '</p>',
          ];
        }
        $item['repository_item'] = [
          '#type' => 'value',
          '#value' => $repo,
        ];
      }

      $form['repositorylist'][] = $item;
    }

    $plugin->setRepository('');

    return $form;
  }

  /**
   * Validation callback for the repository selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function selectRepositoryStepValidate(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getTriggeringElement()['#skip_version_validation'])) {
      if (empty($form_state->getValue('version_item'))) {
        $message = $this->t('Selecting a branch or a tag is required');
        $error_set = FALSE;
        foreach (Element::children($form['repositorylist']) as $key) {
          if (!empty($form['repositorylist'][$key]['version_item'])) {
            $form_state->setError($form['repositorylist'][$key]['version_item'], $message);
            $error_set = TRUE;
            break;
          }
        }

        if (!$error_set) {
          $form_state->setError($form, $message);
        }
      }
    }
  }

  /**
   * Submit handler for the repository selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function selectRepositoryStepSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    $repository = $form_state->getValue('repository_item');
    $version = $form_state->getValue('version_item');
    $form_state->setValue('repository', $repository);
    $form_state->setValue('version', $version);
    $form_state->setValue('step', 'selectFiles');
  }

  /**
   * Form builder for the file selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @param bool $editing
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function selectFilesStepForm(array $form, FormStateInterface $form_state, $editing = FALSE) {
    $this->ensureProcessPlugins();
    $repo_content = $this->getRepoContent($form_state);
    $entity = $this->getEntity();
    $repository = $form_state->getValue('repository');
    $version = $form_state->getValue('version');
    $plugin = $this->createPlugin($form_state);
    $plugin->setRepository($repository);
    $plugin->setVersion($version);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Import title'),
      '#required' => TRUE,
      '#default_value' => $entity->label ? $entity->label : "$repository/$version",
    ];

    $form['repo_content'] = [
      '#type' => 'value',
      '#value' => $repo_content,
    ];

    $repo_content_tree = $form_state->getValue('repo_content_tree') ?: ($editing ? $this->reconstructRepoContentTree($form_state) : NULL);

    $form['repo_content_tree'] = [
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#attributes' => [
        'class' => ['repo-content'],
      ],
      '#title' => $this->t('Repository content'),
      'selected' => [
        '#type' => 'checkbox',
        '#title' => t('Root'),
        '#default_value' => $repo_content_tree['selected'],
      ] + $this->checkboxAjax,
      'contents' => $this->renderRepoContent($repo_content, $repo_content_tree['contents'] ?: [], $repo_content_tree['selected']),
    ];

    $isBranch = $this->isBranch($form_state);

    $form['import_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Import settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      'is_branch' => [
        '#type' => 'value',
        '#value' => $isBranch,
      ],
    ];

    $form['import_settings']['importers'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Content importers'),
      '#description' => $this->t('If the format is wrong you can change it after the import'),
    ];

    $processDefinitions = $this->migrateProcessPluginManager->getDefinitions();

    foreach ($this->config->get('devportal_repo_sync.import')->get('files') as $name => $data) {
      $options = [];
      foreach ($this->processPlugins[$name] as $key => $value) {
        $options[$key] = $this->t('@label <a href=":formathelp">(more info)</a>', [
          '@label' => $value,
          ':formathelp' => $processDefinitions[$key]['formatHelp'],
        ]);
      }

      $form['import_settings']['importers'][$name] = [
        '#type' => 'radios',
        '#title' => $this->t('@label (@pattern)', [
          '@label' => $data['label'],
          '@pattern' => implode(', ', array_map(function ($extension) {
            return "*.{$extension}";
          }, $data['extensions'])),
        ]),
        '#options' => $options,
        '#required' => TRUE,
        '#default_value' => ($editing ? $entity->fileTypes[$name]['converterPlugin'] : NULL) ?: array_keys($this->processPlugins[$name])[0],
      ];
    }

    $form['import_settings']['ref_importers'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API doc importers'),
    ];

    /** @var APIRefType $ref_type */
    foreach (APIRefType::loadMultiple() as $ref_type) {
      $extensionLister = function (array $list) {
        return [
          '@extension-list' => implode(', ', array_map(function ($extension) {
            return "*.{$extension}";
          }, $list)),
        ];
      };

      $form['import_settings']['ref_importers'][$ref_type->id()] = [
        '#type' => 'select',
        '#title' => $ref_type->label(),
        '#options' => [
          RepoImport::REF_IMPORT_SKIP => $this->t('Skip'),
          RepoImport::REF_IMPORT_FILTER => $this->t('Files ending @extension-list', $extensionLister($ref_type->filtered_extensions)),
          RepoImport::REF_IMPORT_ALL => $this->t('Files ending @extension-list', $extensionLister($ref_type->common_extensions)),
        ],
        '#default_value' => $editing ? $entity->refs[$ref_type->id()] : RepoImport::REF_IMPORT_SKIP,
      ];
    }

    $form['update_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Update settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['update_settings']['stage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Publish changes automatically'),
      '#default_value' => $entity->stage,
      '#access' => $isBranch,
    ];

    $webhook_enabled = (bool) $this->config
      ->get('devportal_repo_sync.import')
      ->get('webhook.enabled');
    $can_create_webhook = $webhook_enabled && $plugin->canCreateWebhook();

    $form['import_settings']['enable_webhook'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable webhook'),
      '#default_value' => $can_create_webhook && ((bool) $entity->webhook),
      '#disabled' => !$can_create_webhook,
      '#access' => $webhook_enabled,
    ];

    if ($can_create_webhook && $entity->isNew()) {
      $repo_account = RepoAccount::load($entity->repo_account_id);
      $form['import_settings']['register_webhook'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Register webhook at @provider', [
          '@provider' => $this->getRepositoryProviders()[$repo_account->getProvider()],
        ]),
        '#states' => [
          'visible' => [
            ':input[name="enable_webhook"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    $form_state->set('save_settings', $isBranch);

    $form = $this->bookManager->addFormElements(
      $form,
      $form_state,
      $this->fakeNode,
      $this->currentUser,
      FALSE
    );

    $form['book']['weight']['#access'] = FALSE;
    $form['book']['pid']['#access'] = FALSE;
    $form['book']['bid']['#default_value'] = $entity->bid;
    unset($form['book']['bid']['#options']['new']);
    unset($form['book']['bid']['#ajax']);

    return $form;
  }

  /**
   * Validation callback for the file selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function selectFilesStepValidate(array &$form, FormStateInterface $form_state) {
    $tree = $form_state->getValue('repo_content_tree');
    $value = NULL;
    $triggeringElement = $form_state->getTriggeringElement();
    if ($triggeringElement['#name'] === 'repo_content_tree[selected]') {
      $value = (bool) $triggeringElement['#value'];
    }
    $prefix = 'repo_content_tree[contents]';
    $this->checkAll($tree, $form_state, $prefix, $value);
    $form_state->setValue('repo_content_tree', $tree);
    $user_input = $form_state->getUserInput();
    $this->checkAll($user_input['repo_content_tree'], $form_state, $prefix, $value);
    $form_state->setUserInput($user_input);
  }

  /**
   * Submit handler for the file selection step.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function selectFilesStepSubmit(array &$form, FormStateInterface $form_state) {
    /** @var RepoImport $import */
    $import = $this->entity;

    $properties = [
      'label',
      'repo_account_id',
      'repository',
      'version',
      'stage',
    ];

    foreach ($properties as $property) {
      $import->{$property} = $form_state->getValue($property);
    }

    foreach ($this->config->get('devportal_repo_sync.import')->get('files') as $name => $data) {
      $import->fileTypes[$name] = [
        'extensions' => $data['extensions'],
        'converterPlugin' => $form_state->getValue($name),
      ];
    }

    foreach (APIRefType::loadMultiple() as $ref_type) {
      /** @var APIRefType $ref_type */
      $import->refs[$ref_type->id()] = (int) $form_state->getValue($ref_type->id());
    }

    $directories = [];
    $tree = $form_state->getValue('repo_content_tree');
    $this->findRoots($tree, '', $directories);

    $import->directories = $directories;

    if ($form_state->getValue('enable_webhook')) {
      if (!$import->webhook) {
        $import->generateWebhook();
      }
    }
    else {
      $import->webhook = NULL;
    }

    $book = $form_state->getValue('book');
    $import->bid = $book['bid'];

    $import->save();

    if ($form_state->getValue('enable_webhook') && $form_state->getValue('register_webhook')) {
      $repo_account = RepoAccount::load($import->repo_account_id);
      $sourcePlugin = $this->createProviderInstance($repo_account, $import->repository, $import->version);
      $webhook_url = $import->webhookUrl()->toString();
      $sourcePlugin->authenticate();
      try {
        if (!$sourcePlugin->webhookRegistered($webhook_url)) {
          $sourcePlugin->createWebhook($webhook_url);
        }
      }
      catch (\Exception $ex) {
        \drupal_set_message($this->t('Failed to create a webhook automatically.'));
        watchdog_exception('devportal_repo_sync_webhook', $ex);
      }
    }

    $form_state->setRedirect('entity.repo_import.collection');
  }

  /**
   * A generic AJAX callback that returns the full form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function ajaxCallback(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Recreates the internal repository content tree structure.
   *
   * @param FormStateInterface $form_state
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function reconstructRepoContentTree(FormStateInterface $form_state) {
    $directories = $this->getEntity()->directories;
    $repo_content = $this->getRepoContent($form_state);

    $value = in_array('', $directories);
    return [
      'selected' => $value,
      'contents' => $this->buildLevel($repo_content, $directories, $value),
    ];
  }

  /**
   * Builds a level in the repository content tree when recreating it.
   *
   * @see RepoImportForm::reconstructRepoContentTree()
   *
   * @param array $repo_content
   * @param array $directories
   * @param bool $parent_value
   * @param string $prefix
   *
   * @return array
   */
  private function buildLevel(array $repo_content, array $directories, $parent_value, $prefix = '') {
    $values = [];

    foreach ($repo_content as $name => $status) {
      if ($status) { // skips files
        $filename = $prefix . $name;
        $selected = $parent_value || array_reduce($directories, function ($carry, $directory) use ($filename) {
          return $carry || (strpos($filename, $directory) === 0);
        }, FALSE);
        $values[$filename] = [
          'selected' => $selected,
          'contents' => $this->buildLevel($repo_content[$name], $directories, $selected, $filename),
        ];
      }
    }

    return $values;
  }

  /**
   * Checks all child checkboxes in the form state values or input.
   *
   * @param array $tree
   *   Value tree.
   * @param FormStateInterface $form_state
   * @param string $prefix
   * @param bool|null $check
   *   Whether to force the checked status (parent is checked).
   */
  protected function checkAll(array &$tree, FormStateInterface $form_state, $prefix, $check) {
    if (!isset($tree['selected'])) {
      return;
    }

    $triggeringElement = $form_state->getTriggeringElement();

    if ($check !== NULL) {
      $tree['selected'] = $check;
    }
    if (!empty($tree['contents'])) {
      foreach ($tree['contents'] as $name => $child) {
        $name = "{$prefix}[{$name}]";
        if ($triggeringElement['#name'] === "{$name}[selected]") {
          $check = (bool) $triggeringElement['#value'];
        }
        $this->checkAll($child, $form_state, "{$name}[contents]", $check);
        $tree['contents'][$name] = $child;
      }
    }
  }

  /**
   * Loads the repositories from either the form state, or from the repository.
   *
   * This also saves the repositories into the form state.
   *
   * @param RepositorySource $plugin
   * @param FormStateInterface $form_state
   *
   * @return string[]
   */
  protected function getRepositories(RepositorySource $plugin, FormStateInterface $form_state) {
    $repositories = $form_state->getValue('repositories');
    if ($repositories !== NULL) {
      return $repositories;
    }

    $repositories = $plugin->getRepositories();
    $form_state->setValue('repositories', $repositories);

    return $repositories;
  }

  /**
   * An element validate callback for the custom public repository text field.
   *
   * This checks if the repository name is in the correct format, and validates
   * whether it is accessible for the current repository account.
   *
   * @param array $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function reponameValidate(array $element, FormStateInterface $form_state) {
    $reponame = $element['#value'];
    $plugin = $this->createPlugin($form_state);
    $plugin->setRepository($reponame);

    if ($reponame) {
      $part = '[0-9a-z_-]+';
      $repoRegex = "#{$part}/{$part}#i";
      if (!preg_match($repoRegex, $reponame)) {
        $form_state->setError($element, $this->t('Invalid repository name'));
        return;
      }

      if (!$plugin->repoExists()) {
        $form_state->setError($element, $this->t('The repository does not exists'));
        return;
      }
    }
  }

  /**
   * Submit callback for the 'Add to list' button.
   *
   * Adds a public repository to the repository list.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function addRepo(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    $reponame = $form_state->getValue('reponame');
    if ($reponame) {
      $repositories = $form_state->getValue('repositories', []);
      array_unshift($repositories, $reponame);
      $form_state->setValue('repositories', $repositories);
    }
  }

  /**
   * Loads the branches and tags for a repository from either the form cache or
   * from the repository.
   *
   * If the list is loaded from the repository, it will be stored in the form
   * state.
   *
   * @param RepositorySource $plugin
   * @param FormStateInterface $form_state
   * @param string $full_name
   * @param bool $force_cache
   *   If TRUE, then branches and tags won't be loaded from the remote source.
   * @return array|null
   */
  protected function getBranchesAndTagsFor(RepositorySource $plugin, FormStateInterface $form_state, $full_name, $force_cache = FALSE) {
    $versions = $form_state->getValue('versions', []);
    if (!empty($versions[$full_name])) {
      return $versions[$full_name];
    }
    if ($force_cache) {
      return NULL;
    }

    $versions[$full_name] = $plugin->getBranchesAndTags();
    $form_state->setValue('versions', $versions);

    return $versions;
  }

  /**
   * Submit handler for the repository buttons.
   *
   * This "opens" a repository, displaying its branches and tags.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function expandRepository(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    $full_name = $form_state->getTriggeringElement()['#full_name'];
    $plugin = $this->createPlugin($form_state);
    $plugin->setRepository($full_name);
    $this->getBranchesAndTagsFor($plugin, $form_state, $full_name);
  }

  /**
   * Loads the repo content into the form state.
   *
   * @param FormStateInterface $form_state
   *
   * @return array
   *   The loaded repo content.
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getRepoContent(FormStateInterface $form_state) {
    $repo_content = $form_state->getValue('repo_content');
    if ($repo_content) {
      return $repo_content;
    }

    $repo_content = [];
    $this->expandDirectory($repo_content, $form_state);
    $form_state->setValue('repo_content', $repo_content);

    return $repo_content;
  }

  /**
   * Loads the directory contents from the respository into $repo_content.
   *
   * @param array $repo_content
   * @param FormStateInterface $form_state
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function expandDirectory(array &$repo_content, FormStateInterface $form_state) {
    $path =  [];
    $contents = $this->getDirectoryContents($form_state);
    $this->setRepoContent($repo_content, $path, $contents);
  }

  /**
   * Loads the directory content from the repository.
   *
   * @param FormStateInterface $form_state
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getDirectoryContents(FormStateInterface $form_state) {
    $contents = [];
    $repository = $form_state->getValue('repository');
    $version = $form_state->getValue('version');
    $plugin = $this->createPlugin($form_state);
    $plugin->setRepository($repository);
    $plugin->setVersion($version);

    $tree = $plugin->getTree();

    if ($tree) {
      foreach ($tree['files'] as $file) {
        $path = explode('/', $file);
        $filename = array_pop($path);
        $this->setRepoContent($contents, $path, [$filename => FALSE]);
      }
    }

    return $contents;
  }

  /**
   * Adds the repo content to path.
   *
   * @param array $repo_content
   *   The full repository content array.
   * @param array $path
   *   Path to set.
   * @param array $contents
   *   Content to set.
   */
  protected function setRepoContent(array &$repo_content, array $path, array $contents) {
    if (count($path) === 0) {
      $repo_content += $contents;
    }
    else {
      $current = array_shift($path);
      $current .= '/';
      if (!isset($repo_content[$current])) {
        $repo_content[$current] = [];
      }
      $this->setRepoContent($repo_content[$current], $path, $contents);
    }
  }

  /**
   * Checks if the current version is a branch.
   *
   * @param FormStateInterface $form_state
   *
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function isBranch(FormStateInterface $form_state) {
    $isBranch = $form_state->getValue('is_branch');

    if ($isBranch === NULL) {
      $repository = $form_state->getValue('repository');
      $version = $form_state->getValue('version');
      $plugin = $this->createPlugin($form_state);
      $plugin->setRepository($repository);
      $branchesAndTags = $plugin->getBranchesAndTags();
      $isBranch = $branchesAndTags && in_array($version, $branchesAndTags['branches']);
      $form_state->setValue('is_branch', $isBranch);
    }

    return $isBranch;
  }

  /**
   * Collects the selected subtree roots from the submitted form values.
   *
   * @param array $tree
   *   Current subtree level.
   * @param string $filename
   *   Name of the current level.
   * @param array $roots
   *   Array reference where the results get collected.
   */
  private function findRoots(array $tree, $filename, array &$roots) {
    if (!empty($tree['selected'])) {
      $roots[] = $filename;
      return;
    }

    if (!empty($tree['contents'])) {
      foreach ($tree['contents'] as $filename => $child) {
        $this->findRoots($child, $filename, $roots);
      }
    }
  }

  /**
   * Renders the repository content into a checkbox tree.
   *
   * @param array $repo_content
   *   Current level of the repository content tree.
   * @param array $values
   *   Already existing values from the form state.
   * @param bool $parent_value
   *   Value of the parent checkbox.
   * @param string $prefix
   *   Path prefix. Internal variable, keep it on default.
   *
   * @return array
   *   Checkbox tree.
   */
  protected function renderRepoContent(array $repo_content, array $values, $parent_value = FALSE, $prefix = '') {
    $elements = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'repo-content-directory',
      ],
    ];
    foreach ($repo_content as $name => $status) {
      $filename = $prefix . $name;
      $value = $parent_value || !empty($values[$filename]['selected']);
      if ($status === FALSE) {
        // File.
        $elements[$filename] = [
          '#type' => 'container',
          'label' => [
            '#markup' => Html::escape($name),
          ],
          '#attributes' => [
            'class' => 'repo-content-file',
          ],
        ];
      }
      else {
        // Opened directory.
        $elements[$filename] = [
          '#type' => 'container',
          'selected' => [
            '#type' => 'checkbox',
            '#title' => $name,
            '#default_value' => $value,
            '#disabled' => $parent_value,
          ] + $this->checkboxAjax,
          'contents' => $this->renderRepoContent($repo_content[$name], !empty($values[$filename]['contents']) ? $values[$filename]['contents'] : [], $value, $filename),
        ];
      }
    }

    return $elements;
  }

  /**
   * Puts the step widget to the form.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function stepWidget(array &$form, FormStateInterface $form_state) {
    $step = $form['step']['#default_value'];

    $form['step_widget'] = [
      '#type' => 'container',
      '#weight' => -100,
      '#attributes' => [
        'class' => ['step-widget'],
      ],
    ];

    foreach (static::STEPS as $id => $title) {
      $title = $this->t($title);
      $active = $id === $step ? ' step-widget-active' : '';
      $form['step_widget'][$id] = [
        '#type' => 'markup',
        '#markup' => "<span class=\"step-widget-step{$active}\"><span class=\"step-widget-title\">{$title}</span></span>",
      ];
    }
  }

  /**
   * Makes sure that the process plugins are loaded.
   */
  protected function ensureProcessPlugins() {
    if ($this->processPlugins !== NULL) {
      return;
    }

    $this->processPlugins = [];
    $allProcessPlugins = $this->migrateProcessPluginManager->getDefinitions();
    foreach ($allProcessPlugins as $definition) {
      if (!empty($definition['contentProcessor'])) {
        $this->processPlugins[$definition['contentProcessor']][$definition['id']] = $definition['label'];
      }
    }

    foreach ($this->processPlugins as &$processPlugin) {
      ksort($processPlugin, SORT_STRING);
    }
  }

  /**
   * {@inheritdoc}
   *
   * @return RepoImport
   */
  public function getEntity() {
    return parent::getEntity();
  }

  /**
   * Submit handler that starts the importing batch.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitStartImport(array $form, FormStateInterface $form_state) {
    MigrateBatch::set($this->getEntity());
  }

  /**
   * Returns a list of imported versions (branches and tags) for a repository.
   *
   * @param string $repo
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function existingImportedVersions($repo) {
    $result = $this->entityTypeManager->getStorage('repo_import')->getQuery()
      ->condition('repository', $repo)
      ->execute();

    if (!is_array($result)) {
      return [];
    }

    $entities = $this->entityTypeManager->getStorage('repo_import')->loadMultiple(array_keys($result));

    return array_values(array_map(function ($repo_import) {
      /** @var RepoImport $repo_import */

      return $repo_import->version;
    }, $entities));
  }

  /**
   * Checks if this site runs on the public internet or locally.
   *
   * At the moment, this method only supports IPv4.
   *
   * @return bool
   */
  protected static function isPublicAddress() {
    static $private_addresses = [
      '10.0.0.0/8',
      '172.16.0.0/12',
      '127.0.0.0/8',
      '192.168.0.0/16',
      '100.64.0.0/10',
    ];

    $host = $_SERVER['SERVER_NAME'];
    $ip = (filter_var($host, FILTER_VALIDATE_IP) === FALSE) ?
      gethostbyname("{$host}.") :
      $host;

    foreach ($private_addresses as $cidr) {
      if (static::ipv4_cidr_match($ip, $cidr)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Checks if an IPv4 address is inside a subnet.
   *
   * @param string $ip
   * @param string $cidr
   *
   * @return bool
   */
  protected static function ipv4_cidr_match($ip, $cidr) {
    list($subnet, $mask) = explode('/', $cidr);
    return ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet));
  }

  /**
   * Creates a RepoAccountForm to be used as a subform.
   *
   * @param FormStateInterface $form_state
   *
   * @return RepoAccountForm
   */
  private function createRepoAccountForm(FormStateInterface $form_state) {
    $accountForm = RepoAccountForm::create($this->container);
    $account = RepoAccount::create($form_state->getValue('newaccount') ?: []);
    $accountForm->setEntity($account);

    return $accountForm;
  }

}
