<?php

namespace Drupal\devportal_repo_sync\Entity;

use Drupal\Component\Utility\Random;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;
use Drupal\devportal_api_reference\Entity\APIRef;
use Drupal\devportal_api_reference\Entity\APIRefType;
use Drupal\devportal_migrate_batch\Batch\MigrateBatch;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\node\Entity\Node;
use Drupal\devportal_migrate_batch\Batch\MigrationGeneratorInterface;
use Drupal\devportal_repo_sync\Plugin\migrate\destination\EntityAlwaysNewRevisionDestination;
use Drupal\devportal_repo_sync\Plugin\migrate\source\RepositorySource;

/**
 * @ConfigEntityType(
 *   id = "repo_import",
 *   label = @Translation("Repository import"),
 *   handlers = {
 *     "view_builder" = "Drupal\devportal_repo_sync\Controller\RepoImportViewBuilder",
 *     "list_builder" = "Drupal\devportal_repo_sync\Controller\RepoImportListBuilder",
 *     "form" = {
 *       "add" = "Drupal\devportal_repo_sync\Form\RepoImportForm",
 *       "edit" = "Drupal\devportal_repo_sync\Form\RepoImportForm",
 *       "delete" = "Drupal\devportal_repo_sync\Form\RepoImportDeleteForm",
 *     },
 *    "access" = "Drupal\devportal_repo_sync\RepoImportAccessControlHandler",
 *   },
 *   config_prefix = "repo_import",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "uuid" = "uuid",
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/repo-import/{repo_import}",
 *     "edit-form" = "/admin/structure/repo-import/{repo_import}/edit",
 *     "delete-form" = "/admin/structure/repo-import/{repo_import}/delete",
 *     "webhook" = "/repo-import/{repo_import}/webhook/{repo_import_webhook}",
 *   },
 * )
 */
class RepoImport extends ConfigEntityBase implements ConfigEntityInterface, MigrationGeneratorInterface {

  const MIGRATION_NAMESPACE = 'repository_imports';

  const MIGRATION_GROUP = 'devportal_repo_sync';

  const REF_IMPORT_SKIP = 0;
  const REF_IMPORT_FILTER = 1;
  const REF_IMPORT_ALL = 2;

  /**
   * Entity id.
   *
   * @var string
   */
  public $id;

  /**
   * Entity UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * Entity label.
   *
   * @var string
   */
  public $label;

  /**
   * RepoAccount UUID.
   *
   * @var string
   *
   * @see RepoAccount
   */
  public $repo_account_id;

  /**
   * Repository identitifer.
   *
   * @var string
   */
  public $repository;

  /**
   * Branch or tag or commit.
   *
   * @var string
   */
  public $version;

  /**
   * List of directories to import.
   *
   * If one of the items in the array is an empty string, then everything will
   * be imported.
   *
   * @var string[]
   */
  public $directories;

  /**
   * Import data for content (html) file types.
   *
   * @var array
   */
  public $fileTypes;

  /**
   * Whether to publish changes automatically or not.
   *
   * @var bool
   */
  public $stage = TRUE;

  /**
   * Timestamp of last save.
   *
   * @var int
   */
  public $changed;

  /**
   * Hash for the webhook support.
   *
   * @var string
   */
  public $webhook;

  /**
   * Book ID.
   *
   * @var int
   */
  public $bid;

  /**
   * Filename mask settings for reference imports.
   *
   * @var int[]
   */
  public $refs;

  /**
   * {@inheritdoc}
   */
  public function id() {
    // Restrict the ID length to 22 characters. Otherwise some migration table
    // names would be too long and exceed the maximum 63 characters. For example
    // see $this->getLatestImport().
    return substr(md5($this->uuid()), 0, 22);
  }

  /**
   * Returns the entity label.
   *
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * Regenerates the webhook string.
   *
   * @return string
   *   The new webhook string.
   */
  public function generateWebhook() {
    return $this->webhook = (new Random())->name(128);
  }

  /**
   * Creates an absolute URL to the webhook endpoint if enabled.
   *
   * @return Url|null
   */
  public function webhookUrl() {
    return $this->webhook ? Url::fromRoute('entity.repo_import.webhook', [
      'repo_import' => $this->id(),
      'repo_import_webhook' => $this->webhook,
    ], ['absolute' => TRUE]) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($this->webhook && $rel === 'webhook') {
      $uri_route_parameters['repo_import_webhook'] = $this->webhook;
    }
    else {
      $uri_route_parameters['repo_import_webhook'] = '-';
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $this->changed = \Drupal::time()->getRequestTime();
  }

  /**
   * Looks at the latest imported nodes to find out the latest import timestamp.
   *
   * @return int|null
   *   Timestamp of the latest import or null, if there wasn't any.
   */
  public function getLatestImport() {
    try {
      $tableName = MigrateBatch::migrationTableName('migrate_map', static::MIGRATION_NAMESPACE, $this->getContentMigration());
      if (\Drupal::database()->schema()->tableExists($tableName)) {
        return \Drupal::database()->select($tableName, 'm')
          ->fields('m', ['last_imported'])
          ->orderBy('last_imported', 'DESC')
          ->range(0, 1)
          ->execute()
          ->fetchField() ?: NULL;
      }
    }
    catch (\Exception $ex) {
      watchdog_exception('repo_import', $ex);
    }

    return NULL;
  }

  /**
   * Returns the generated regexp filters.
   *
   * @return array
   *   Keys are entity types, values are the regexp filters.
   */
  protected function getFilters() {
    $escaped_directories = '(' . implode('|', array_map(function ($directory) {
      return preg_quote($directory, '/');
    }, $this->directories)) . ')';

    $ref_extensions = $this->mergeRefExtensions() ?: '\x00';
    return [
      'file' => "/^{$escaped_directories}[\s\S]*\.(?:png|jpe?g|gif|webp|bmp|tiff?)$/",
      'file:revision' => "/^{$escaped_directories}[\s\S]*\.({$ref_extensions})$/",
      'node' => "/^{$escaped_directories}[\s\S]*\.(?:{$this->mergeExtensions()})$/",
      'api_ref' => "/^{$escaped_directories}[\s\S]*\.({$ref_extensions})$/",
    ];
  }

  /**
   * Creates a regexp of all filetype extensions.
   *
   * @return string
   *   Merged regexp.
   */
  protected function mergeExtensions() {
    return implode('|', array_map('preg_quote', array_unique(call_user_func_array('array_merge', array_map(function ($fileType) {
      return $fileType['extensions'];
    }, $this->fileTypes)))));
  }

  /**
   * Creates a regexp of all reference file type extensions.
   *
   * @return string
   *   Merged regexp.
   */
  protected function mergeRefExtensions() {
    return implode('|', array_map('preg_quote', array_unique(call_user_func_array('array_merge', array_map(function ($ref_type) {
      /** @var APIRefType $ref_type */
      switch (isset($this->refs[$ref_type->id()]) ? $this->refs[$ref_type->id()] : static::REF_IMPORT_SKIP) {
        case static::REF_IMPORT_FILTER:
          return $ref_type->filtered_extensions;

        case static::REF_IMPORT_ALL:
          return $ref_type->common_extensions;
      }

      return [];
    }, APIRefType::loadMultiple())))));
  }

  /**
   * Migration id of the file migration.
   *
   * @return string
   *   Migration plugin ID.
   */
  public function getFileMigration() {
    return $this->id() . '__file';
  }

  /**
   * Migration of of the revisioning-emulation file migration.
   *
   * @return string
   *   Migration plugin ID.
   */
  public function getRevisionedFileMigration() {
    return $this->id() . '__r_file';
  }

  /**
   * Migration id of the content migration.
   *
   * @return string
   *   Migration plugin ID.
   */
  public function getContentMigration() {
    return $this->id() . '__content';
  }

  /**
   * Migration id of the reference migration.
   *
   * @return string
   *   Reference plugin ID.
   */
  public function getRefMigration() {
    return $this->id() . '__ref';
  }

  /**
   * Converts this entity into migration configs.
   *
   * @param MigrateSourcePluginManager|null $sourcePluginManager
   *   Source plugin manager.
   *
   * @return array
   *   Migration definitions.
   */
  public function toMigrations(MigrateSourcePluginManager $sourcePluginManager = NULL) {
    if ($sourcePluginManager === NULL) {
      $sourcePluginManager = \Drupal::service('plugin.manager.migrate.source');
    }

    $latest_import = $this->getLatestImport();

    $migrations = [];

    $filters = $this->getFilters();
    $file_import_name = $this->getFileMigration();
    $revisioned_file_import_name = $this->getRevisionedFileMigration();
    $content_import_name = $this->getContentMigration();
    $ref_import_name = $this->getRefMigration();

    /** @var RepoAccount $account */
    $account = RepoAccount::load($this->repo_account_id);

    $source_def = [
      'plugin' => $account->getProvider(),
      'method' => $account->getMethod(),
      'identifier' => $account->getIdentifier(),
      'secret' => $account->getSecret(),
      'repository' => $this->repository,
      'version' => $this->version,
      'latest_import' => $latest_import ? date('c', $latest_import) : NULL,
    ];

    $sourcePluginAnnotation = $sourcePluginManager->getDefinition($source_def['plugin']);

    $file_migration_generator = function ($import_name, $filter, $emulate_revisions) use ($source_def) {
      return [
        'dependencies' => [
          'module' => ['file'],
        ],
        'id' => $import_name,
        'migration_group' => static::MIGRATION_GROUP,
        'label' => t("%label image", ['%label' => $this->getLabel()]),
        'source' => $source_def + [
          'filter' => $filter,
          'emulateRevisions' => $emulate_revisions,
        ],
        'process' => [
          '__tmp_file' => [
            'plugin' => 'cat',
            'source' => 'content',
          ],
          '__extension' => [
            'plugin' => 'pathinfo',
            'options' => 'extension',
            'source' => 'filename',
          ],
          '__hashed_filename' => [
            'plugin' => 'hash',
            'source' => [
              'filename',
              'repository',
              'version',
            ],
          ],
          '__scheme' => [
            'plugin' => 'default_value',
            'default_value' => file_default_scheme() . '://',
          ],
          '__extension_separator' => [
            'plugin' => 'default_value',
            'default_value' => '.',
          ],
          '__assembled_destination_path' => [
            'plugin' => 'concat',
            'delimiter' => '',
            'source' => [
              '@__scheme',
              '@__hashed_filename',
              '@__extension_separator',
              '@__extension',
            ],
          ],
          'uri' => [
            'plugin' => 'file_copy',
            'source' => [
              '@__tmp_file',
              '@__assembled_destination_path',
            ],
          ],
        ],
        'destination' => [
          'plugin' => 'entity:file',
        ],
      ];
    };

    $migrations[$file_import_name] = $file_migration_generator($file_import_name, $filters['file'], FALSE);

    $migrations[$revisioned_file_import_name] = $file_migration_generator($revisioned_file_import_name, $filters['file:revision'], TRUE);

    $migrations[$content_import_name] = [
      'dependencies' => [
        'module' => 'node',
      ],
      'migration_dependencies' => [
        'required' => [
          static::MIGRATION_NAMESPACE . ':' . $file_import_name,
        ],
      ],
      'id' => $content_import_name,
      'migration_group' => static::MIGRATION_GROUP,
      'label' => t('%label content', ['%label' => $this->getLabel()]),
      'source' => $source_def + [
        'filter' => $filters['node'],
      ],
      'process' => [
        'type' => [
          'plugin' => 'default_value',
          'default_value' => 'imported_content',
        ],
        'field_ic_provider' => [
          'plugin' => 'default_value',
          'default_value' => $account->getProvider(),
        ],
        'field_ic_body/format' => [
          'plugin' => 'default_value',
          'default_value' => 'imported_content',
        ],
        'field_ic_filename' => [
          'plugin' => 'get',
          'source' => 'filename',
        ],
        'field_ic_repository' => [
          'plugin' => 'get',
          'source' => 'repository',
        ],
        'field_ic_version' => [
          'plugin' => 'get',
          'source' => 'version',
        ],
        '__html_body' => [
          'plugin' => 'smart_converter',
          'fileTypes' => $this->fileTypes,
          'source' => [
            'filename',
            'content',
          ],
        ],
        '__transformed_title' => [
          'plugin' => 'extract_title',
          'source' => '@__html_body',
        ],
        'title' => [
          'plugin' => 'coalesce',
          'source' => [
            '@__transformed_title',
            'filename',
          ],
        ],
        'status' => [
          'plugin' => 'default_value',
          'default_value' => TRUE,
        ],
        '__processed_body' => [
          'plugin' => 'reference_extract',
          'filter' => "/\.{$this->mergeExtensions()}$/",
          'linkPattern' => $sourcePluginAnnotation['linkPattern'],
          'rawLinkPattern' => $sourcePluginAnnotation['linkRawPattern'],
          'linkDestination' => '__extracted_links',
          'source' => '@__html_body',
        ],
        '__processed_links' => [
          [
            'plugin' => 'fixlink',
            'source' => [
              '@__extracted_links',
              'repository',
              'version',
            ],
          ],
          [
            'plugin' => 'migration_lookup',
            'migration' => [
              static::MIGRATION_NAMESPACE . ':' . $file_import_name,
              static::MIGRATION_NAMESPACE . ':' . $content_import_name,
            ],
            'stub_id' => static::MIGRATION_NAMESPACE . ':' . $content_import_name,
          ],
        ],
        'field_ic_body/value' => [
          'plugin' => 'reference_assemble',
          'tagEntityMap' => [
            'a' => 'node',
            'img' => 'file',
          ],
          'source' => [
            '@__processed_body',
            '@__processed_links',
            '@__extracted_links',
          ],
        ],
      ],
      'destination' => [
        'plugin' => EntityAlwaysNewRevisionDestination::PREFIX . ':node',
        'overwrite_properties' => [
          'field_ic_provider',
          'field_ic_filename',
          'field_ic_repository',
          'field_ic_version',
          'title',
          'status',
          'field_ic_body/value',
          'field_ic_body/format',
        ],
        'publish_changes' => $this->stage,
      ],
    ];

    if ($this->bid) {
      $migrations[$content_import_name]['process']['book/bid'] = [
        'plugin' => 'default_value',
        'default_value' => $this->bid,
      ];

      $migrations[$content_import_name]['process']['book/pid'] = [
        'plugin' => 'default_value',
        'default_value' => $this->bid,
      ];
    }

    $migrations[$ref_import_name] = [
      'dependencies' => [
        'module' => 'devportal_api_reference',
      ],
      'id' => $ref_import_name,
      'migration_group' => static::MIGRATION_GROUP,
      'label' => t('%label reference', ['%label' => $this->getLabel()]),
      'source' => $source_def + [
        'filter' => $filters['api_ref'],
      ],
      'process' => [
        'type' => [
          'plugin' => 'ref_type_negotiator',
          'config' => $this->refs,
          'source' => 'filename',
        ],
        'title' => [
          'plugin' => 'get',
          'source' => 'filename',
        ],
        'source' => [
          'plugin' => 'migration_lookup',
          'migration' => [
            static::MIGRATION_NAMESPACE . ':' . $revisioned_file_import_name,
          ],
          'source' => [
            'repository',
            'filename',
            'version',
            'commit',
          ],
        ],
      ],
      'destination' => [
        'plugin' => EntityAlwaysNewRevisionDestination::PREFIX . ':api_ref',
        'overwrite_properties' => [
          'name',
          'source',
        ],
        'publish_changes' => TRUE,
      ],
    ];

    return $migrations;
  }

  /**
   * {@inheritdoc}
   */
  public function cleanUp($all = FALSE) {
    if ($all) {
      $this->cleanUpAll();
    }
    else {
      $this->cleanUpNew();
    }
  }

  /**
   * Looks at the recent commits and deletes deleted content.
   */
  protected function cleanUpNew() {
    $last_import = $this->getLatestImport();
    if (!$last_import) {
      return;
    }
    $last_import = date_iso8601($last_import);

    $source = $this->getSourcePlugin();
    $repo_account = RepoAccount::load($this->repo_account_id);

    $changed_files = $source->getChangedFilesSince($last_import);
    $filters = $this->getFilters();
    if (!empty($filters['node'])) {
      $changed_files = array_filter($changed_files, function ($file) use ($filters) {
        return preg_match($filters['node'], $file);
      });
    }

    $deleted_files = array_keys(array_filter($changed_files, function ($status) {
      return $status === RepositorySource::FILE_DELETED;
    }));

    if ($deleted_files) {
      $to_unpublish = \Drupal::entityQuery('node')
        ->condition('type', 'imported_content')
        ->condition('field_ic_repository', $this->repository)
        ->condition('field_ic_version', $this->version)
        ->condition('field_ic_provider', $repo_account->getProvider())
        ->condition('field_ic_filename', $deleted_files, 'IN')
        ->execute();

      $unpublish_nodes = Node::loadMultiple($to_unpublish);

      foreach ($unpublish_nodes as $node) {
        /** @var Node $node */
        $node->setUnpublished();
        $node->save();
      }
    }
  }

  /**
   * Compares the full repository content with the local content.
   *
   * Excess content will be removed.
   */
  protected function cleanUpAll() {
    $repo_account = RepoAccount::load($this->repo_account_id);
    $source = $this->getSourcePlugin();
    $tree = $source->getTree();

    if (empty($tree) || empty($tree['files'])) {
      return;
    }

    $files = $tree['files'];

    $filters = $this->getFilters();
    if (!empty($filters['node'])) {
      $files = array_filter($files, function ($file) use ($filters) {
        return preg_match($filters['node'], $file);
      });
    }

    if ($files) {
      $to_unpublish = \Drupal::entityQuery('node')
        ->condition('type', 'imported_content')
        ->condition('field_ic_repository', $this->repository)
        ->condition('field_ic_version', $this->version)
        ->condition('field_ic_provider', $repo_account->getProvider())
        ->condition('field_ic_filename', $files, 'NOT IN')
        ->execute();
      $unpublish_nodes = Node::loadMultiple($to_unpublish);

      foreach ($unpublish_nodes as $node) {
        /** @var Node $node */
        $node->setUnpublished();
        $node->save();
      }
    }
  }

  /**
   * Returns a source plugin instance for this import.
   *
   * @return RepositorySource
   */
  protected function getSourcePlugin() {
    $repo_account = RepoAccount::load($this->repo_account_id);
    /** @var MigrateSourcePluginManager $source_manager */
    $source_manager = \Drupal::service('plugin.manager.migrate.source');

    return $source_manager->createInstance($repo_account->getProvider(), [
      'method' => $repo_account->getMethod(),
      'identifier' => $repo_account->getIdentifier(),
      'secret' => $repo_account->getSecret(),
      'repository' => $this->repository,
      'version' => $this->version,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getFilterPattern() {
    $namespace = preg_quote(static::MIGRATION_NAMESPACE);
    $import_id = preg_quote($this->id(), '/');
    return "/^{$namespace}:{$import_id}__/";
  }

  /**
   * {@inheritdoc}
   */
  public function getMigrationAfterCallbacks() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    $migrations = [
      'file' => $this->getFileMigration(),
      'file:revision' => $this->getRevisionedFileMigration(),
      'node' => $this->getContentMigration(),
      'api_ref' => $this->getRefMigration(),
    ];

    parent::delete();

    foreach ($migrations as $entity_type => $migration) {
      if (($pos = strpos($entity_type, ':')) !== FALSE) {
        $entity_type = substr($entity_type, 0, $pos);
      }

      $maptable = MigrateBatch::migrationTableName('migrate_map', static::MIGRATION_NAMESPACE, $migration);
      $messagetable = MigrateBatch::migrationTableName('migrate_message', static::MIGRATION_NAMESPACE, $migration);

      if (\Drupal::database()->schema()->tableExists($maptable)) {
        $existing_entities = \Drupal::database()->select($maptable, 'm')
          // @TODO all entity migrations have only one destid, but this should not be assumed.
          ->fields('m', ['destid1'])
          ->execute()
          ->fetchCol();

        if (is_array($existing_entities)) {
          $existing_entities = array_filter($existing_entities);

          if ($existing_entities) {
            $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
            $storage->delete($storage->loadMultiple($existing_entities));
          }
        }
      }

      foreach ([$maptable, $messagetable] as $table) {
        try {
          \Drupal::database()->schema()->dropTable($table);
        }
        catch (\Exception $ex) {
          watchdog_exception('devportal_repo_sync', $ex);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function relatedGenerators() {
    $maptable = MigrateBatch::migrationTableName('migrate_map', static::MIGRATION_NAMESPACE, $this->getRefMigration());
    $ref_ids = \Drupal::database()->select($maptable, 'm')
      ->fields('m', ['destid1'])
      ->execute()
      ->fetchCol();

    return $ref_ids ? APIRef::loadMultiple($ref_ids) : [];
  }

}
