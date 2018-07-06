<?php

namespace Drupal\devportal_repo_sync\Plugin\QueueWorker;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *   id = "file_cleanup",
 *   title = @Translation("File import cleanup"),
 *   cron = { "time" = 10 },
 * )
 */
class FileCleanup extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    Connection $database,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->logger = $logger;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $database = $container->get('database');
    $logger = $container->get('logger.factory')->get('file_cleanup');
    $entityTypeManager = $container->get('entity_type.manager');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $database,
      $logger,
      $entityTypeManager
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    /** @var string $import */
    $import = $data->import;
    /** @var array $filenames */
    $filenames = $data->filenames;

    $q = $this->database->select('import_map', 'm')
      ->fields('m', ['entity_type', 'entity_id']);
    $q->condition('import', $import);
    $q->condition('filename', $filenames, 'NOT IN');
    $result = $q->execute();
    $remove = [];
    while (($row = $result->fetchAssoc())) {
      $remove[$row['entity_type']][] = $row['entity_id'];
    }

    foreach ($remove as $entity_type => $ids) {
      $storage = $this->entityTypeManager->getStorage($entity_type);
      $entities = $storage->loadMultiple($ids);
      $storage->delete($entities);
    }
  }

}
