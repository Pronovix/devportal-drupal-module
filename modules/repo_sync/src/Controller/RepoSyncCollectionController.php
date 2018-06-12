<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\devportal_repo_sync\Exception\DevportalRepoSyncConnectionException;
use Drupal\devportal_repo_sync\Service\Client;

/**
 * Class RepoSyncCollectionController.
 */
class RepoSyncCollectionController extends ControllerBase {

  /**
   * Creates a table from the imported repositories.
   *
   * @return array|bool
   *   Return table array.
   *
   * @throws \Exception
   */
  public function content() {
    $config = $this->config('devportal_repo_sync.config');
    $client = new Client($config->get('uuid'), hex2bin($config->get('secret')), $config->get('service'));

    try {
      $rows = [];
      $result = $client("GET", "/api/import", NULL);
      $result = json_decode(array_pop($result));

      foreach ($result->items as $item) {
        $rows[] = [
          $item->Label,
          $item->ID,
          $item->Owner,
          [
            'data' => [
              '#type' => 'operations',
              '#links' => [
                'view' => [
                  'title' => $this->t('View'),
                  'url' => Url::fromRoute('devportal_repo_sync.controller_view', ['uuid' => $item->ID]),
                ],
                'edit' => [
                  'title' => $this->t('Update'),
                  'url' => Url::fromRoute('devportal_repo_sync.update_form', ['uuid' => $item->ID]),
                ],
                'delete' => [
                  'title' => $this->t('Delete'),
                  'url' => Url::fromRoute('devportal_repo_sync.delete_form', ['uuid' => $item->ID]),
                ],
              ],
            ],
          ],
        ];
      }
    }
    catch (DevportalRepoSyncConnectionException $e) {
      $this->messenger()->addError($e->getMessage());
      watchdog_exception('repo_sync', $e);
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
