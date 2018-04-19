<?php

namespace Drupal\devportal_migrate_batch\Batch;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\migrate_drupal_ui\Batch\MigrateMessageCapture;
use Drupal\devportal_migrate_batch\MigrateExecutableLimiter;

/**
 * Helper function collection for the content import batch.
 */
class MigrateBatch {

  /**
   * Default message length.
   */
  const MESSAGE_LENGTH = 20;

  /**
   * Sets a batch to import a repository.
   *
   * This function should be called inside a form submit.
   *
   * @param MigrationGeneratorInterface $generator
   *   Repository import entity to import.
   *
   * @see \batch_set()
   */
  public static function set(MigrationGeneratorInterface $generator) {
    $generator->cleanUp(FALSE);
    batch_set([
      'title' => t('Importing content'),
      'operations' => [
        [[static::class, 'markUpdates'], [$generator]],
        [[static::class, 'import'], [$generator]],
        [[static::class, 'importRelated'], [$generator]],
        [[static::class, 'after'], [$generator]],
      ],
      'finished' => [static::class, 'finished'],
    ]);
  }

  /**
   * Callback for the batch import operation.
   *
   * @param MigrationGeneratorInterface $generator
   *   Repository import generator.
   * @param array $context
   *   Batch context.
   *
   * @return bool
   *   TRUE if items are left, FALSE if the migration is completed.
   * @throws \Drupal\migrate\MigrateException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public static function import(MigrationGeneratorInterface $generator, array &$context) {
    $migrations = static::getMigrations($generator->getFilterPattern());
    return static::run($migrations, $context);
  }

  /**
   * Imports the "related" content.
   *
   * @param \Drupal\devportal_migrate_batch\Batch\MigrationGeneratorInterface $rootGenerator
   *   Repository import generator.
   * @param array $context
   *   Batch context.
   *
   * @throws \Drupal\migrate\MigrateException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public static function importRelated(MigrationGeneratorInterface $rootGenerator, array &$context) {
    $generators = $rootGenerator->relatedGenerators();
    $migrations = [];
    foreach ($generators as $generator) {
      $current_migrations = static::getMigrations($generator->getFilterPattern());
      $migrations = array_merge($migrations, $current_migrations);
    }
    $migrations = array_unique($migrations, SORT_REGULAR);

    if ($migrations) {
      static::run($migrations, $context);
    }
  }

  /**
   * Marks all generations to need an update.
   *
   * @param MigrationGeneratorInterface $generator
   *   Repository import generator.
   * @param array $context
   *   Batch context.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public static function markUpdates(MigrationGeneratorInterface $generator, array &$context) {
    $migrations = static::getMigrations($generator->getFilterPattern());

    foreach ($migrations as $migration) {
      static::prepareMap($migration);
    }

    $context['finished'] = 1.0;
  }

  /**
   * Runs tasks after the import is finished.
   *
   * @param MigrationGeneratorInterface $generator
   *   Repository import generator.
   *
   * @see \Drupal\devportal_migrate_batch\Batch\MigrateBatch::set()
   */
  public static function after(MigrationGeneratorInterface $generator) {
    $after = $generator->getMigrationAfterCallbacks();

    foreach ($generator->relatedGenerators() as $related_generator) {
      $after = array_merge($after, $related_generator->getMigrationAfterCallbacks());
    }

    foreach ($after as $callback) {
      call_user_func_array($callback[0], $callback[1]);
    }
  }

  /**
   * Callback for the batch import operation.
   *
   * @param MigrationInterface[] $migrations
   *   A list of migrations to run.
   * @param array $context
   *   Batch context.
   *
   * @return bool
   *   TRUE if items are left, FALSE if the migration is completed.
   * @throws \Drupal\migrate\MigrateException
   */
  public static function run(array $migrations, array &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = 0;
      foreach ($migrations as $migration) {
        $count = static::count($migration);
        $context['sandbox']['max'] += $count;
        $context['sandbox']['counters'][$migration->id()] = $count;
      }
      $context['sandbox']['messages'] = [];
    }

    if ($context['sandbox']['max'] === 0) {
      return FALSE;
    }

    $messages = new MigrateMessageCapture();
    $limit = 10;

    $leftover = FALSE;

    foreach ($migrations as $id => $migration) {
      $count = &$context['sandbox']['counters'][$migration->id()];
      if ($count > 0) {
        $executable = new MigrateExecutableLimiter($migration, $messages, NULL, $limit);
        $executable->import();

        if ($count > $limit) {
          $context['sandbox']['progress'] += $limit;
          $count -= $limit;
          $leftover = TRUE;
          break;
        }
        else {
          $context['sandbox']['progress'] += $count;
          $count = 0;
          $limit -= $count;
        }
      }
    }

    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    $context['message'] = t('Importing %progress / %max', [
      '%progress' => $context['sandbox']['progress'],
      '%max' => $context['sandbox']['max'],
    ]);

    foreach ($messages->getMessages() as $message) {
      $context['sandbox']['messages'][] = $message;
      \Drupal::logger('migrate_batch')->error($message);
    }
    $message_count = count($context['sandbox']['messages']);
    for ($index = max(0, $message_count - self::MESSAGE_LENGTH); $index < $message_count; $index++) {
      $context['message'] = "{$context['sandbox']['messages'][$index]}<br />\n{$context['message']}";
    }
    if ($message_count > self::MESSAGE_LENGTH) {
      $context['message'] .= '&hellip;';
    }

    return $leftover;
  }

  /**
   * Sets all imported content in a migration map to STATUS_NEEDS_UPDATE.
   *
   * @param MigrationInterface $migration
   *   Migration to change the map of.
   */
  public static function prepareMap(MigrationInterface $migration) {
    $migration->getIdMap()->prepareUpdate();
  }

  /**
   * Collects the migrations for a RepoImport entity.
   *
   * @param string $filter_pattern
   *   Filter pattern regexp.
   *
   * @return MigrationInterface[]
   *   Matching migrations.
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public static function getMigrations($filter_pattern) {
    static::clearMigrationDiscoveryCache();

    /** @var MigrationPluginManager $manager */
    $manager = \Drupal::service('plugin.manager.migration');
    $plugins = $manager->createInstances([]);

    $migrations = [];

    foreach ($plugins as $id => $migration) {
      if (preg_match($filter_pattern, $id)) {
        $migrations[$id] = $migration;
      }
    }

    return $migrations;
  }

  /**
   * Counts the items in a migration.
   *
   * @param MigrationInterface $migration
   *   Migration to count from.
   *
   * @return int
   *   Counted items.
   */
  public static function count(MigrationInterface $migration) {
    return $migration->getSourcePlugin()->count();
  }

  /**
   * Implements callback_batch_finished().
   */
  public static function finished($success, $results, $operations) {
  }

  /**
   * Deletes the 'migration_plugin' entry from cache.discovery_migration.
   */
  protected static function clearMigrationDiscoveryCache() {
    /** @var CacheBackendInterface $cache */
    $cache = \Drupal::service('cache.discovery_migration');
    $cache->delete('migration_plugins');
  }

  /**
   * Creates the table name for migration related tables.
   *
   * @param string $prefix
   *   Can be 'migration_map' or 'migration_message'.
   * @param string $namespace
   *   Migration namespace.
   * @param string $migration_id
   *   Migration ID.
   *
   * @see \Drupal\migrate\Plugin\migrate\id_map\Sql::init()
   *
   * @return string
   *   Table name.
   */
  public static function migrationTableName($prefix, $namespace, $migration_id) {
    $prefix_length = strlen(\Drupal::database()->tablePrefix());
    return Unicode::substr($prefix . '_' . Unicode::strtolower(str_replace(':', '__', $namespace . ':' . $migration_id)), 0, 63 - $prefix_length);
  }

}
