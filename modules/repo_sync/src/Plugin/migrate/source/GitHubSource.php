<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\source;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Github\Api\CurrentUser;
use Github\Api\GitData\Trees;
use Github\Api\Repo;
use Github\Api\Repository\Commits;
use Github\Client;

/**
 * @MigrateSource(
 *   id = "github",
 *   label = "GitHub",
 *   linkPattern = "https://github.com/{repository}/blob/{version}/{path}",
 *   linkRawPattern = "https://raw.githubusercontent.com/{repository}/{version}/{path}",
 *   editLinkPattern = "https://github.com/{repository}/edit/{version}/{path}",
 *   methods = {
 *     "url_token" = @Translation("URL Token"),
 *     "url_client_id" = @Translation("Client ID"),
 *     "http_token" = @Translation("HTTP Token"),
 *     "http_password" = @Translation("HTTP Password"),
 *   },
 * )
 */
class GitHubSource extends RepositorySource {

  /**
   * Internal GitHub client object.
   *
   * @var Client
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $loggerChannel, CacheBackendInterface $cacheBackend, $cache = TRUE, MigrationInterface $migration = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $loggerChannel, $cacheBackend, $cache, $migration);
    $this->client = new Client();
  }

  /**
   * Converts github file statuses.
   *
   * @param string $status
   *
   * @return string
   */
  private static function githubStatusToFileStatus($status) {
    switch ($status) {
      case 'added':
        return static::FILE_ADDED;

      case 'changed':
      case 'modified':
        return static::FILE_CHANGED;

      case 'deleted':
      case 'removed':
        return static::FILE_DELETED;
    }

    return static::FILE_UNKNOWN;
  }

  /**
   * {@inheritdoc}
   */
  protected function authenticateClient() {
    return $this->errorHandler(function () {
      $this->client->authenticate($this->identifier, $this->secret, $this->method);
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAuthentication() {
    return $this->errorHandler(function () {
      $currentUser = new CurrentUser($this->client);
      $currentUser->show();
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function loadBranchesAndTags() {
    return (array) $this->errorHandler(function () {
      list($user_name, $repo_name) = explode('/', $this->repository);
      $repository = new Repo($this->client);

      $branches = $repository->branches($user_name, $repo_name);
      $tags = $repository->tags($user_name, $repo_name);

      $map = function ($item) {
        return $item['name'];
      };

      return [
        'branches' => array_map($map, $branches),
        'tags' => array_map($map, $tags),
      ];
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function loadTree() {
    return (array) $this->errorHandler(function () {
      list($user_name, $repo_name) = explode('/', $this->repository);
      $trees = new Trees($this->client);

      $tree = $trees->show($user_name, $repo_name, $this->version, TRUE);

      $directories = [];
      $files = [];
      $revisions = [];

      if (!$tree['truncated']) {
        foreach ($tree['tree'] as $item) {
          switch ($item['type']) {
            case 'blob':
              $files[] = $item['path'];
              $revisions[$item['path']] = $item['sha'];
              break;

            case 'tree':
              $directories[] = $item['path'];
              $revisions[$item['path']] = $item['sha'];
              break;
          }
        }
      }
      else {
        $this->logger->error('Repository tree of @repository at @version is truncated', [
          '@repository' => $this->repository,
          '@version' => $this->version,
        ]);
      }

      natcasesort($directories);
      natcasesort($files);

      return [
        'directories' => $directories,
        'files' => $files,
        'revisions' => $revisions,
      ];
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function loadRepositories() {
    return (array) $this->errorHandler(function () {
      $currentUser = new CurrentUser($this->client);
      $repos = $currentUser->repositories('all');

      return array_map(function ($repo) {
        return $repo['full_name'];
      }, $repos);
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function loadFile($path) {
    return (string) $this->errorHandler(function () use ($path) {
      $linkRawPattern = $this->pluginDefinition['linkRawPattern'];
      $url = strtr($linkRawPattern, [
        '{repository}' => $this->repository,
        '{version}' => $this->version,
        '{path}' => $path,
      ]);
      return $this->client->getHttpClient()->get($url)->getBody()->getContents();
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function loadChangedFilesSince($since) {
    return (array) $this->errorHandler(function () use ($since) {
      $compared = $this->compareCommits($since);
      $filelist = [];
      if (!empty($compared['files'])) {
        foreach ($compared['files'] as $file) {
          $filelist[$file['filename']] = static::githubStatusToFileStatus($file['status']);
        }
      }

      return $filelist;
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function loadDiffSince($since) {
    return (string) $this->errorHandler(function () use ($since) {
      $compared = $this->compareCommits($since);
      $diff_url = empty($compared['diff_url']) ? NULL : $compared['diff_url'];
      return $diff_url ?
        $this->client->getHttpClient()->get($diff_url)->getBody()->getContents() :
        '';
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function loadRepoExists() {
    return $this->errorHandler(function () {
      list($user_name, $repo_name) = explode('/', $this->repository);
      $repo = new Repo($this->client);
      try {
        $repo->show($user_name, $repo_name);
        return TRUE;
      }
      catch (\Exception $ex) {
        return FALSE;
      }
    });
  }

  /**
   * {@inheritdoc}
   */
  public function webhookRegistered($webhook_url) {
    return $this->errorHandler(function () use ($webhook_url) {
      list($user_name, $repo_name) = explode('/', $this->repository);
      $repo = new Repo($this->client);

      foreach ($repo->hooks()->all($user_name, $repo_name) as $hook) {
        if ($hook['config']['url'] === $webhook_url) {
          return TRUE;
        }
      }

      return FALSE;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function createWebhook($webhook_url) {
    return $this->errorHandler(function () use ($webhook_url) {
      list($user_name, $repo_name) = explode('/', $this->repository);
      $repo = new Repo($this->client);
      $repo->hooks()->create($user_name, $repo_name, [
        'name' => 'web',
        'config' => [
          'url' => $webhook_url,
          'content_type' => 'json',
        ],
        'events' => ['push'],
        'active' => TRUE,
      ]);
    });
  }

  /**
   * {@inheritdoc}
   */
  public static function filterWebhook($version, $response) {
    return $response['ref'] === "refs/heads/{$version}";
  }

  /**
   * Compares two commits and returns the result in GitHub's API format.
   *
   * @param string $since
   *   The old commit's timestamp in ISO8601 format.
   *
   * @return array
   *   Comparison data.
   */
  protected function compareCommits($since) {
    return (array) $this->errorHandler(function () use ($since) {
      if (!$since) {
        return [];
      }

      list($user_name, $repo_name) = explode('/', $this->repository);
      $commits = new Commits($this->client);
      $commitlist = $commits->all($user_name, $repo_name, [
        'since' => $since,
        'sha' => $this->version,
      ]);
      if (count($commitlist) === 0) {
        return [];
      }

      $current_commit = end($commitlist);
      $parent = end($current_commit['parents']);
      $since_sha = $parent['sha'];

      return $commits->compare($user_name, $repo_name, $since_sha, $this->version);
    });
  }

  /**
   * {@inheritdoc}
   */
  protected function checkWebhookCreation() {
    list($user_name, $repo_name) = explode('/', $this->repository);
    try {
      $repo = new Repo($this->client);
      $collaborators = $repo->collaborators()->all($user_name, $repo_name);
      foreach ($collaborators as $collaborator) {
        if ($collaborator['login'] === $this->identifier) {
          return $collaborator['permissions']['admin'];
        }
      }
    }
    catch (\Exception $ex) {
      watchdog_exception('devportal_repo_sync', $ex);
    }

    return FALSE;
  }

  protected function errorHandler(callable $action) {
    try {
      return $action();
    }
    catch (\Exception $ex) {
      watchdog_exception('devportal_github', $ex);
      \Drupal::messenger()->addError(t('Unable to load data from github.'));
    }

    return NULL;
  }

}
