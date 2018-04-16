<?php

namespace Drupal\devportal_migrate_batch;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Executes a migration, but limits the number of source rows that will be migrated.
 */
class MigrateExecutableLimiter extends MigrateExecutable {

  /**
   * Number of source rows to process.
   *
   * @var int
   */
  protected $limit;

  /**
   * {@inheritdoc}
   */
  public function __construct(MigrationInterface $migration, MigrateMessageInterface $message, EventDispatcherInterface $event_dispatcher = NULL, $limit = 5) {
    parent::__construct($migration, $message, $event_dispatcher);
    $this->limit = $limit;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkStatus() {
    $status = parent::checkStatus();

    if ($status === MigrationInterface::RESULT_COMPLETED) {
      if ($this->limit > 0) {
        $this->limit--;
      }
      else {
        $status = MigrationInterface::RESULT_INCOMPLETE;
      }
    }

    return $status;
  }

}
