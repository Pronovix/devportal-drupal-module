<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Utility\ThemeRegistry;
use Drupal\devportal_api_reference\Entity\APIRef;
use Drupal\devportal_migrate_batch\Batch\MigrateBatch;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\devportal_repo_sync\Entity\RepoImport;
use Drupal\devportal_repo_sync\Form\RepoImportViewForm;
use Drupal\devportal_repo_sync\RepoSourceInfoTrait;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Shows a repository import entity.
 */
class RepoImportViewBuilder extends EntityViewBuilder {

  use RepoSourceInfoTrait;

  /**
   * @var FormBuilderInterface
   */
  protected $form_builder;

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * @var DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function __construct(MigrateSourcePluginManager $migrateSourcePluginManager, Connection $connection, DateFormatterInterface $dateFormatter, FormBuilderInterface $form_builder, EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, Registry $theme_registry = NULL) {
    parent::__construct($entity_type, $entity_manager, $language_manager, $theme_registry);
    $this->form_builder = $form_builder;
    $this->migrateSourcePluginManager = $migrateSourcePluginManager;
    $this->connection = $connection;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var MigrateSourcePluginManager $source_plugin_manager */
    $source_plugin_manager = $container->get('plugin.manager.migrate.source');

    /** @var Connection $connection */
    $connection = $container->get('database');

    /** @var DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');

    /** @var FormBuilderInterface $form_builder */
    $form_builder = $container->get('form_builder');

    /** @var EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    /** @var LanguageManagerInterface $language_manager */
    $language_manager = $container->get('language_manager');

    /** @var ThemeRegistry $theme_registry */
    $theme_registry = $container->get('theme.registry');

    return new static($source_plugin_manager, $connection, $date_formatter, $form_builder, $entity_type, $entity_manager, $language_manager, $theme_registry);
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    /** @var RepoImport $entity */

    $build = parent::getBuildDefaults($entity, $view_mode);

    $build['list'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Last updated'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No imported content available.'),
      '#rows' => [],
    ];

    $node_map_table_name = MigrateBatch::migrationTableName('migrate_map', RepoImport::MIGRATION_NAMESPACE, $entity->getContentMigration());
    $ref_map_table_name = MigrateBatch::migrationTableName('migrate_map', RepoImport::MIGRATION_NAMESPACE, $entity->getRefMigration());

    $database_schema = \Drupal::database()->schema();
    if ($database_schema->tableExists($node_map_table_name) && $database_schema->tableExists($ref_map_table_name)) {
      $total = $this->connection->query("
      SELECT (
        SELECT COUNT(*) FROM {{$node_map_table_name}}
      ) + (
        SELECT COUNT(*) FROM {{$ref_map_table_name}}
      );
    ")->fetchField();
      $num_per_page = 25;
      $page = pager_default_initialize($total, $num_per_page);
      $offset = $num_per_page * $page;

      $entity_list = $this->connection->query("
      SELECT entity_type, entity_id, updated FROM (
        SELECT 'node' AS entity_type, n.nid AS entity_id, v.revision_timestamp AS updated
          FROM {node} n
          JOIN {{$node_map_table_name}} m ON n.nid = m.destid1
          JOIN {node_revision} v ON n.nid = v.nid AND n.vid = v.vid
        UNION
        SELECT 'api_ref' AS entity_type, r.id AS entity_id, r.changed AS updated
          FROM {api_ref_field_data} r
          JOIN {{$ref_map_table_name}} m ON r.id = m.destid1
      ) AS query
      ORDER BY updated DESC
      LIMIT {$num_per_page}
      OFFSET {$offset}
    ")->fetchAll(\PDO::FETCH_ASSOC);

      $lister = function ($entity_type) use ($entity_list) {
        return array_map(function ($item) {
          return $item['entity_id'];
        }, array_filter($entity_list, function ($item) use ($entity_type) {
          return $item['entity_type'] = $entity_type;
        }));
      };

      $entities = [];
      $entities['node'] = Node::loadMultiple($lister('node'));
      $entities['api_ref'] = APIRef::loadMultiple($lister('api_ref'));

      foreach ($entity_list as $entity_record) {
        /** @var Node|APIRef $listed_entity */
        $listed_entity = $entities[$entity_record['entity_type']][$entity_record['entity_id']];

        $row = [
          'data' => [
            'name' => [
              'data' => [
                '#type' => 'link',
                '#title' => $listed_entity->label(),
                '#url' => $listed_entity->toUrl(),
              ],
            ],
            'updated' => $this->dateFormatter->format($entity_record['updated']),
            'operations' => [
              'data' => [
                '#type' => 'operations',
                '#links' => $this->entityManager->getListBuilder($entity_record['entity_type'])->getOperations($listed_entity),
              ],
            ],
          ],
        ];

        if ($listed_entity->getEntityTypeId() === 'node') {
          $row['data']['name']['data']['#suffix'] = !$listed_entity->isPublished() ? " ({$this->t('excluded')})" : '';
        }

        $build['list']['#rows'][] = $row;
      }
    }

    if (($webhook_url = $entity->webhookUrl())) {
      $build['webhook'] = [
        '#type' => 'container',
        'webhook_label' => [
          '#title_display' => 'before',
          '#type' => 'label',
          '#title' => $this->t('Webhook endpoint'),
        ],
        'webhook_url' => [
          '#title' => $webhook_url->toString(),
          '#type' => 'link',
          '#url' => $webhook_url,
        ],
      ];
    }

    $form = new RepoImportViewForm($entity);
    $build['form'] = $this->form_builder->getForm($form);

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

}
