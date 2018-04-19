<?php

namespace Drupal\devportal_migrate_batch\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\devportal_migrate_batch\Batch\MigrationGeneratorInterface;
use Drupal\devportal_migrate_batch\Batch\MigrateBatch;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *   id = "import_queue",
 *   title = @Translation("Processes an import"),
 *   cron = { "time" = 240 }
 * )
 */
class ImportQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    /** @var MigrationGeneratorInterface $generator */
    $generator = call_user_func_array([$data['class'], 'load'], [$data['id']]);

    if ($generator) {
      if (MigrateBatch::import($generator, $data['context'])) {
        static::createItem($generator, $this->container, $data['context']);
      }
      else {
        MigrateBatch::after($generator);
      }
    }
    else {
      \Drupal::logger('devportal_repo_sync')->warning('Missing generator instance', [
        'class' => $data['class'],
        'id' => $data['id'],
      ]);
    }
  }

  /**
   * Creates and adds an item for this queue.
   *
   * @param MigrationGeneratorInterface $generator
   *   Migration generator.
   * @param ContainerInterface|null $container
   *   Service container.
   * @param array $context
   *   For internal use only, do not set.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public static function createItem(MigrationGeneratorInterface $generator, ContainerInterface $container = NULL, array $context = NULL) {
    if ($container === NULL) {
      $container = \Drupal::getContainer();
    }

    /** @var QueueFactory $queue_factory */
    $queue_factory = $container->get('queue');
    $queue = $queue_factory->get('import_queue', TRUE);

    if ($context === NULL) {
      $context = [];
      MigrateBatch::markUpdates($generator, $context);
    }

    $queue->createItem([
      'class' => get_class($generator),
      'id' => $generator->id(),
      'context' => $context,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setContainer($container);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($container, $configuration, $plugin_id, $plugin_definition);
  }

}
