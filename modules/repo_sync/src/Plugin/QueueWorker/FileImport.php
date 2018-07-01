<?php

namespace Drupal\devportal_repo_sync\Plugin\QueueWorker;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\devportal_repo_sync\FileTypeManager;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *   id = "file_import",
 *   title = @Translation("File import worker"),
 *   cron = { "time" = 10 },
 * )
 */
class FileImport extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Manager for the FileType plugin.
   *
   * @var \Drupal\devportal_repo_sync\FileTypeManager
   */
  protected $fileTypeManager;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Connection to the database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Connector service for the repo importer.
   *
   * @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector
   */
  protected $connector;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $fileTypeManager = $container->get('plugin.manager.file_type');
    $logger = $container->get('logger.factory')->get('file_import');
    $client = $container->get('http_client');
    $database = $container->get('database');
    $entityTypeManager = $container->get('entity_type.manager');
    $connector = $container->get('devportal_repo_sync.connection');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $fileTypeManager,
      $logger,
      $client,
      $database,
      $entityTypeManager,
      $connector
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    $plugin_definition,
    FileTypeManager $fileTypeManager,
    LoggerInterface $logger,
    Client $client,
    Connection $database,
    EntityTypeManagerInterface $entityTypeManager,
    RepoSyncConnector $connector
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileTypeManager = $fileTypeManager;
    $this->logger = $logger;
    $this->client = $client;
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->connector = $connector;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $importid = $data->import;
    $filename = $data->filename;
    $processed = $data->processed;
    $original = $data->original;

    $import = $this->connector->getImport($importid);

    $manager = $this->fileTypeManager->lookupPlugin($filename);

    if (!$manager) {
      $this->logger->error('Unable to find a handler for %filename', [
        '%filename' => $filename,
      ]);
      return;
    }

    $response = $this->client->get($processed ?: $original);
    if ($response->getStatusCode() >= 400) {
      $this->logger->error('Cannot download file %filename (%code)', [
        '%filename' => $filename,
        '%code' => $response->getStatusCode(),
      ]);
      throw new SuspendQueueException();
    }

    try {
      $entity = $this->loadEntityForFile($importid, $filename);
      $isNew = $entity === NULL;
      $entity = $manager->import($entity, $filename, $import["BasePath"], $response->getBody());
      if ($entity) {
        $entity->save();
        if ($isNew) {
          $this->saveMapping($importid, $filename, $entity);
        }
      }
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return;
    }
  }

  /**
   * Tries to load an entity for a given file.
   *
   * @param string $import
   *   Import id.
   * @param string $filename
   *   Filename.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity if found, null otherwise.
   */
  protected function loadEntityForFile(string $import, string $filename): ?EntityInterface {
    $query = $this->database->select('import_map', 'i')
      ->fields('i', ['entity_type', 'entity_id']);
    $query
      ->condition('import', $import)
      ->condition('filename', $filename);
    $result = $query->execute()->fetchAssoc();
    if (!$result) {
      return NULL;
    }

    return $this->entityTypeManager->getStorage($result['entity_type'])->load($result['entity_id']);
  }

  /**
   * Saves a mapping to the database.
   *
   * @param string $import
   *   Import id.
   * @param string $filename
   *   Filename.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The mapped entity.
   */
  protected function saveMapping(string $import, string $filename, EntityInterface $entity) {
    $this->database->merge('import_map')
      ->keys([
        'import' => $import,
        'filename' => $filename,
      ])
      ->fields([
        'entity_type' => $entity->getEntityTypeId(),
        'entity_id' => $entity->id(),
      ])
      ->execute();
  }

}
