<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\source;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrateSourceInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\devportal_repo_sync\RepositoryIterator;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract source plugin for repositories.
 */
abstract class RepositorySource extends SourcePluginBase implements MigrateSourceInterface, ContainerFactoryPluginInterface {

  const FILE_ADDED = 'added';
  const FILE_CHANGED = 'changed';
  const FILE_DELETED = 'deleted';
  const FILE_UNKNOWN = 'unknown';

  /**
   * An optional authentication method for the provider.
   *
   * @var string
   */
  protected $method;

  /**
   * Account identifier, e.g. username.
   *
   * @var string
   */
  protected $identifier;

  /**
   * Account secret, e.g. password.
   *
   * @var string
   */
  protected $secret;

  /**
   * Repository identifier.
   *
   * @var string
   */
  protected $repository;

  /**
   * Branch or tag.
   *
   * @var string
   */
  protected $version;

  /**
   * File filtering regexp.
   *
   * Normally this is used to restrict directories.
   *
   * @var string
   */
  protected $filter;

  /**
   * @var Client
   */
  protected $http_client;

  /**
   * @var bool
   */
  protected $authenticated;

  /**
   * @var LoggerChannelInterface
   */
  protected $logger;

  /**
   * ISO8601 date of the latest import or NULL if this is the first time.
   *
   * @var string|null
   */
  protected $latest_import;

  /**
   * @var CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * @var bool
   */
  protected $enableCache;

  /**
   * If set to TRUE, initializeIterator() will return only the updated content.
   *
   * This is needed, so code that assumes a "normal" migration (e.g. drush
   * integration) would get an expected response.
   *
   * @var bool
   */
  public $updatesOnly = FALSE;

  /**
   * Emulates revisioning.
   *
   * This is useful for entities that does not support revisioning. This will
   * create a new entity for each revision.
   *
   * @var bool
   */
  protected $emulateRevisions;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $loggerChannel, CacheBackendInterface $cacheBackend, $cache = TRUE, MigrationInterface $migration = NULL) {
    // It is not possible to construct a SourcePluginBase without a migration.
    // There are cases when the RepositorySource based source plugins are
    // needed, but not as a migration plugin, rather as an information source
    // about the remote repository. In those cases, $migration will be NULL.
    //
    // Fortunately SourcePluginBase::__construct() only sets up values that are
    // needed for migration only, so the parent constructor can be skipped.
    if ($migration) {
      parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    }

    $this->logger = $loggerChannel;
    $this->cacheBackend = $cacheBackend;
    $this->enableCache = $cache;
    if (isset($configuration['method'])) {
      $this->method = $configuration['method'];
    }
    if (isset($configuration['identifier'])) {
      $this->identifier = $configuration['identifier'];
    }
    if (isset($configuration['secret'])) {
      $this->secret = $configuration['secret'];
    }
    if (isset($configuration['repository'])) {
      $this->repository = $configuration['repository'];
    }
    if (isset($configuration['version'])) {
      $this->version = $configuration['version'];
    }
    if (isset($configuration['filter'])) {
      $this->filter = $configuration['filter'];
    }
    if (isset($configuration['emulateRevisions'])) {
      $this->emulateRevisions = (bool) $configuration['emulateRevisions'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return strtr($this->pluginDefinition['linkRawPattern'], [
      'repository' => $this->repository,
      'version' => $this->version,
      'path' => '',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    /** @var LoggerChannelFactory $loggerFactory */
    $loggerFactory = $container->get('logger.factory');

    /** @var CacheBackendInterface $cacheBackend */
    $cacheBackend = $container->get('cache.repositorysource');

    /** @var ConfigFactory $config_factory */
    $config_factory = $container->get('config.factory');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $loggerFactory->get('devportal_repo_sync'),
      $cacheBackend,
      $config_factory->get('devportal_repo_sync.import')->get('cache'),
      $migration
    );
  }

  /**
   * Filters files with the filter regexp if defined.
   *
   * @param array $files
   *
   * @param array $updated_files
   *
   * @return array
   */
  protected function filterFiles(array $files, array $updated_files = []) {
    $files = $this->filter ? array_filter($files, function ($filename) {
      return preg_match($this->filter, $filename);
    }) : $files;

    return $files;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'repository' => $this->t('Repository'),
      'version' => $this->t('Version'),
      'filename' => $this->t('Filename'),
      'content' => $this->t('Content'),
      'commit' => $this->t('Commit hash'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = [
      'repository' => [
        'type' => 'string',
      ],
      'filename' => [
        'type' => 'string',
      ],
      'version' => [
        'type' => 'string',
      ],
    ];

    if ($this->emulateRevisions) {
      $ids['commit'] = [
        'type' => 'string',
      ];
    }

    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $tree = $this->getTree();

    $files = $this->filterFiles($tree['files']);
    return new RepositoryIterator(
      $this->repository,
      $this->version,
      $files,
      $tree['revisions'],
      function ($file) {
        return $this->getFile($file);
      }
    );
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate($check = FALSE) {
    if (!$this->authenticated) {
      $this->authenticateClient();
    }
    if ($check) {
      $this->checkAuthentication();
    }
    $this->authenticated = TRUE;
  }

  /**
   * Adds authentication information to the internal client.
   *
   * @return void
   */
  abstract protected function authenticateClient();

  /**
   * Verifies if the internal client is authenticated.
   *
   * @return void
   */
  abstract protected function checkAuthentication();

  /**
   * Files and directories from the repository.
   *
   * @return array
   *   Map with two keys: directories and files. Both keys contain an array of
   *   paths.
   */
  abstract protected function loadTree();

  /**
   * Retrieves a list of branches and tags.
   *
   * @return \string[][]
   *   The first dimension is either 'tags' or 'branches',
   *   the second is the list.
   */
  abstract protected function loadBranchesAndTags();

  /**
   * Retrieves the list of all repositories that the user is associated with.
   *
   * @return string[]
   */
  abstract protected function loadRepositories();

  /**
   * Retrieves a content of a file or directory.
   *
   * @param string $path
   *
   * @return array
   *   Keys are 'type', which can be either 'file' or 'directory'.
   *   If the type is file, then the 'content' key contains the file's content.
   *   If the type is directory, then the 'content' key contains a list of items.
   *   In case of a file, a 'modified' key contains the last modification date,
   *   in ISO8601 format.
   */
  abstract protected function loadFile($path);

  /**
   * Returns the list of changed files.
   *
   * @param string $since
   *   Format: ISO8601.
   * @return array
   *   The key is file filename, and the value is either
   *   FILE_ADDED, FILE_CHANGED, FILE_DELETED or FILE_UNKNOWN.
   */
  abstract protected function loadChangedFilesSince($since);

  /**
   * Returns the diff of changes.
   *
   * @param string $since
   *   Format: ISO8601.
   * @return string
   */
  abstract protected function loadDiffSince($since);

  /**
   * Checks if the plugin is authenticated.
   *
   * @return bool
   */
  public function isAuthenticated() {
    return $this->authenticated;
  }

  /**
   * Checks if a given repository exists and accessible.
   *
   * @return bool
   */
  abstract protected function loadRepoExists();

  /**
   * Checks if a webhook is registered.
   *
   * @param string $webhook_url
   *
   * @return bool
   */
  abstract public function webhookRegistered($webhook_url);

  /**
   * Creates a webhook.
   *
   * @param string $webhook_url
   */
  abstract public function createWebhook($webhook_url);

  /**
   * Checks if the current user can create a webhook for the current repository.
   *
   * @return bool
   */
  abstract protected function checkWebhookCreation();

  /**
   * Checks if the current user can create a webhook for the current repository.
   *
   * @return bool
   */
  public function canCreateWebhook() {
    return $this->withCache(__FUNCTION__, strtotime('+1 hour'), function () {
      return $this->checkWebhookCreation();
    });
  }

  /**
   * Filters invalid webhooks.
   *
   * @param string $version
   *   Branch or tag.
   * @param mixed $response
   *
   * @return bool
   */
  public static function filterWebhook($version, $response) {
    return TRUE;
  }

  /**
   * @return mixed
   */
  public function getRepository() {
    return $this->repository;
  }

  /**
   * @param mixed $repository
   *
   * @return RepositorySource
   */
  public function setRepository($repository) {
    $this->repository = $repository;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * @param mixed $version
   *
   * @return RepositorySource
   */
  public function setVersion($version) {
    $this->version = $version;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getFilter() {
    return $this->filter;
  }

  /**
   * @param mixed $filter
   *
   * @return RepositorySource
   */
  public function setFilter($filter) {
    $this->filter = $filter;
    return $this;
  }

  /**
   * Creates the cache id for this instance.
   *
   * @param string $type
   *
   * @return string
   */
  protected function constructCacheID($type) {
    return "devportal_repo_sync:source:{$this->getPluginId()}:{$this->identifier}:{$this->repository}:{$this->version}:{$type}";
  }

  /**
   * Constructs a global cache tag for this instance.
   *
   * @return string
   */
  protected function getCacheTag() {
    return "repositorysource:{$this->getPluginId()}:{$this->identifier}:{$this->repository}:{$this->version}";
  }

  /**
   * Caching wrapper function.
   *
   * @param string $type
   *   Identifier to help differentiate the cached data.
   * @param int $expires
   *   Expiration timestamp.
   * @param callable $loader
   *   A callable that loads the data.
   *
   * @return mixed
   */
  protected function withCache($type, $expires, callable $loader) {
    if (!$this->enableCache) {
      return $loader();
    }

    $cid = $this->constructCacheID($type);
    if (($item = $this->cacheBackend->get($cid)) !== FALSE) {
      return $item->data;
    }

    $this->authenticate();
    $data = $loader();
    if ($this->isDataCacheable($data)) {
      $this->cacheBackend->set($cid, $data, $expires, [
        $this->getCacheTag(),
      ]);
    }

    return $data;
  }

  /**
   * Decides whether the response data is cacheable.
   *
   * @param mixed $data
   *   Response data.
   *
   * @return bool
   *   Decision.
   */
  protected function isDataCacheable($data) {
    if ($data === NULL) {
      return FALSE;
    }

    if (is_array($data) && count($data) === 0) {
      return FALSE;
    }

    if ($data === "") {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Files and directories from the repository.
   *
   * @see RepositorySource::loadTree()
   *
   * @return array
   *   Map with two keys: directories and files. Both keys contain an array of
   *   paths.
   */
  public function getTree() {
    return $this->withCache('tree', strtotime('+1 day'), function () {
      return $this->loadTree();
    });
  }

  /**
   * Retrieves a list of branches and tags.
   *
   * @see RepositorySource::loadBranchesAndTags()
   *
   * @return string[][]
   *   The first dimension is either 'tags' or 'branches',
   *   the second is the list.
   */
  public function getBranchesAndTags() {
    return $this->withCache('branchesandtags', strtotime('+1 hour'), function () {
      return $this->loadBranchesAndTags();
    });
  }

  /**
   * Retrieves the list of all repositories that the user is associated with.
   *
   * @see RepositorySource::getRepositories()
   *
   * @return string[]
   */
  public function getRepositories() {
    return $this->withCache('repositories', strtotime('+1 hour'), function () {
      return $this->loadRepositories();
    });
  }

  /**
   * Retrieves a content of a file or directory.
   *
   * @see RepositorySource::loadFile()
   *
   * @param string $path
   * @return array Keys are 'type', which can be either 'file' or 'directory'.
   *   Keys are 'type', which can be either 'file' or 'directory'.
   *   If the type is file, then the 'content' key contains the file's content.
   *   If the type is directory, then the 'content' key contains a list of items.
   *   In case of a file, a 'modified' key contains the last modification date,
   *   in ISO8601 format.
   */
  public function getFile($path) {
    return $this->withCache("file.{$path}", strtotime('+1 hour'), function () use ($path) {
      return $this->loadFile($path);
    });
  }

  /**
   * Returns the list of changed files.
   *
   * @see RepositorySource::loadChangedFilesSince()
   *
   * @param string $since
   *   Format: ISO8601.
   * @return array
   *   The key is file filename, and the value is either
   *   FILE_ADDED, FILE_CHANGED, FILE_DELETED or FILE_UNKNOWN.
   */
  public function getChangedFilesSince($since) {
    return $this->withCache("changedfilessince.{$since}", strtotime('+1 hour'), function () use ($since) {
      return $this->loadChangedFilesSince($since);
    });
  }

  /**
   * Returns the diff of changes.
   *
   * @see RepositorySource::loadDiffSince()
   *
   * @param string $since
   *   Format: ISO8601.
   * @return string
   */
  public function getDiffSince($since) {
    return $this->withCache("diffsince.{$since}", strtotime('+1 hour'), function () use ($since) {
      return $this->loadDiffSince($since);
    });
  }

  /**
   * Checks if a given repository exists and accessible.
   *
   * @see RepositorySource::loadRepoExists()
   *
   * @return bool
   */
  public function repoExists() {
    return $this->withCache('repoexists', strtotime('+1 hour'), function () {
      return $this->loadRepoExists();
    });
  }

  /**
   * Resets the cache for this instance.
   */
  public function resetCache() {
    Cache::invalidateTags([$this->getCacheTag()]);
  }

}
