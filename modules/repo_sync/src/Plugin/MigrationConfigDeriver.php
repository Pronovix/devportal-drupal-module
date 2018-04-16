<?php

namespace Drupal\devportal_repo_sync\Plugin;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\devportal_repo_sync\Entity\RepoImport;

/**
 * Derives migration configs from RepoImport entities.
 */
class MigrationConfigDeriver extends DeriverBase {

  /**
   * @var MigrateSourcePluginManager
   */
  protected $sourcePluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    // For some reason ContainerFactoryPluginInterface doesn't work.
    $this->sourcePluginManager = \Drupal::service('plugin.manager.migrate.source');
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $migrations = [];

    foreach (RepoImport::loadMultiple() as $import) {
      /** @var RepoImport $import */
      $migrations += $import->toMigrations($this->sourcePluginManager);
    }

    return $migrations;
  }

}
