<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepoSyncCollectionController.
 */
class RepoSyncCollectionController extends ControllerBase {

  /**
   * Drupal\devportal_repo_sync\Service\RepoSyncConnector definition.
   *
   * @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector
   */
  protected $devportalRepoSyncConnection;

  /**
   * Constructs a new RepoSyncCollectionController object.
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
   * Creates a table from the imported repositories.
   *
   * @return array|bool
   *   Return table array.
   */
  public function content() {
    $result = $this->devportalRepoSyncConnection->getImports();

    $rows = [];
    foreach ($result["items"] as $item) {
      $rows[] = [
        Link::fromTextAndUrl($item['Label'], Url::fromRoute('devportal_repo_sync.controller_view', ['uuid' => $item['ID']])),
        $item["ID"],
        $item["Owner"],
        [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('devportal_repo_sync.update_form', ['uuid' => $item["ID"]]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('devportal_repo_sync.delete_form', ['uuid' => $item["ID"]]),
              ],
            ],
          ],
        ],
      ];
    }

    $build = [
      '#type' => 'table',
      '#caption' => $this->t('Repository Synchronization settings overview.'),
      '#header' => [
        $this->t('Name'),
        $this->t('UUID'),
        $this->t('Owner'),
        $this->t('Operations'),
      ],
      '#rows' => $rows ?: [['Nothing to display.']],
      '#description' => $this->t('Repository Synchronization settings overview.'),
    ];

    return $build;
  }

}
