<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
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
  protected $devportalRepoSyncConnection;

  /**
   * Constructs a new RepoSyncViewController object.
   *
   * @param \Drupal\devportal_repo_sync\Service\RepoSyncConnector $devportal_repo_sync_connection
   */
  public function __construct(RepoSyncConnector $devportal_repo_sync_connection) {
    $this->devportalRepoSyncConnection = $devportal_repo_sync_connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('devportal_repo_sync.connection')
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
   *
   * @throws \Exception
   */
  public function content($uuid) {
    $result = $this->devportalRepoSyncConnection->getImport($uuid);
    $rows = [];
    foreach ($result as $key => $value) {
      $rows[] = [$key, $value];
    }
    $build = [
      '#type' => 'table',
      '#caption' => $this->t('Repository Synchronization settings overview.'),
      '#header' => [
        $this->t('Key'),
        $this->t('Value'),
      ],
      '#rows' => $rows ?: [['Nothing to display.']],
      '#description' => $this->t('Repository Synchronization settings overview.'),
    ];
    return $build;
  }

}
