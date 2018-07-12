<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepoSyncViewController.
 */
class RepoSyncViewController extends ControllerBase {

  /**
   * Drupal\devportal_repo_sync\Service\RepoSyncConnector definition.
   *
   * @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector
   */
  protected $connection;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new RepoSyncViewController object.
   *
   * @param \Drupal\devportal_repo_sync\Service\RepoSyncConnector $connection
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct(RepoSyncConnector $connection, Connection $database) {
    $this->connection = $connection;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('devportal_repo_sync.connection'),
      $container->get('database')
    );
  }

  /**
   * View an import.
   *
   * @param string $uuid
   *   The UUID of the imported repository.
   *
   * @return array
   *   Return content render array.
   */
  public function content($uuid) {
    $import = $this->connection->getImport($uuid);
    $import_rows = [];

    foreach ($import as $key => $value) {
      $import_rows[] = [
        $key,
        [
          'class' => 'value',
          'data' => $value,
        ],
      ];
    }

    $entities = [];
    $rows = [];

    $query = $this->database->select('import_map', 'm')
      ->fields('m', ['filename', 'entity_type', 'entity_id'])
      ->orderBy('filename');
    $query
      ->condition('import', $uuid);

    foreach ($query->execute()->fetchAll(\PDO::FETCH_ASSOC) as $row) {
      $entities[$row['entity_type']][] = $row['entity_id'];
      $rows[] = $row;
    }

    foreach ($entities as $entity_type => $ids) {
      $entities[$entity_type] = $this->entityTypeManager()->getStorage($entity_type)->loadMultiple($ids);
    }

    return [
      '#attached' => [
        'library' => [
          'devportal_repo_sync/import-page',
        ],
      ],
      'import_details' => [
        '#type' => 'details',
        '#title' => $this->t('Import details'),
        '#open' => FALSE,
        'import' => [
          '#type' => 'table',
          '#caption' => $this->t('Repository Synchronization settings overview.'),
          '#header' => [
            $this->t('Key'),
            $this->t('Value'),
          ],
          '#rows' => $import_rows ?: [['Nothing to display.']],
          '#description' => $this->t('Repository Synchronization settings overview.'),
          '#attributes' => [
            'class' => ['import-details'],
          ],
        ],
      ],
      'entities' => [
        '#type' => 'table',
        '#caption' => $this->t('Imported entities'),
        '#header' => [
          $this->t('File'),
          $this->t('Entity type'),
          $this->t('Imported entity'),
        ],
        '#rows' => array_map(function (array $row) use (&$entities): array {
          /** @var \Drupal\Core\Entity\EntityInterface $entity */
          $entity = $entities[$row['entity_type']][$row['entity_id']] ?? NULL;
          if (!$entity) {
            return [
              $row['filename'],
              $row['entity_type'],
              $this->t('Missing entity'),
            ];
          }

          return [
            $row['filename'],
            $row['entity_type'],
            $entity instanceof FileInterface ? Link::fromTextAndUrl($entity->label(), Url::fromUri(file_create_url($entity->getFileUri()))) : $entity->toLink(),
          ];
        }, $rows),
      ],
    ];
  }

}
