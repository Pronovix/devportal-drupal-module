<?php

namespace Drupal\devportal_repo_sync;

use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\devportal_repo_sync\Entity\RepoAccount;
use Drupal\devportal_repo_sync\Plugin\migrate\source\RepositorySource;

trait RepoSourceInfoTrait {

  /**
   * @var MigrateSourcePluginManager
   */
  protected $migrateSourcePluginManager;

  /**
   * Maps the repository provider definitions.
   *
   * @param callable $mapper
   *   A function that takes the definition of the repository provider plugin
   *   and returns something based on it.
   *
   * @return array
   *   The key is the id of the repository provider plugin, and the value is
   *   what the mapper function returns.
   */
  protected function mapRepositoryProviders(callable $mapper) {
    $providers = [];
    $definitions = $this->migrateSourcePluginManager->getDefinitions();

    foreach ($definitions as $id => $definition) {
      if (is_subclass_of($definition['class'], RepositorySource::class)) {
        $providers[$id] = $mapper($definition);
      }
    }

    return $providers;
  }

  /**
   * Helper function that returns the list of the repository providers.
   *
   * @return array
   */
  protected function getRepositoryProviders() {
    return $this->mapRepositoryProviders(function($definition) {
      return $definition['label'];
    });
  }

  /**
   * Creates a plugin instance.
   *
   * @param RepoAccount $repo_account
   * @param string $repository
   * @param string $version
   *
   * @return RepositorySource
   */
  protected function createProviderInstance(RepoAccount $repo_account, $repository, $version) {
    return $this->migrateSourcePluginManager->createInstance($repo_account->getProvider(), [
      'method' => $repo_account->getMethod(),
      'identifier' => $repo_account->getIdentifier(),
      'secret' => $repo_account->getSecret(),
      'repository' => $repository,
      'version' => $version,
    ]);
  }

}
