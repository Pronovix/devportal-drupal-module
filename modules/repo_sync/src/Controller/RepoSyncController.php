<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\devportal_repo_sync\Service\Client;

/**
 * Class RepoSyncController.
 */
class RepoSyncController extends ControllerBase {

  /**
   * Creates a table from the imported repositories.
   *
   * @return array
   *   Return table array.
   */
  public function content() {
    $config = $this->config('devportal_repo_sync.config');
    $client = new Client($config->get('uuid'), hex2bin($config->get('secret')), "http://service:8000");

    try {
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
                  'url' => Url::fromRoute('devportal_repo_sync.devportal_repo_sync_config_form'),
                ],
                'edit' => [
                  'title' => $this->t('Edit'),
                  'url' => Url::fromRoute('devportal_repo_sync.devportal_repo_sync_config_form'),
                ],
                'delete' => [
                  'title' => $this->t('Delete'),
                  'url' => Url::fromRoute('devportal_repo_sync.devportal_repo_sync_delete_form', ['uuid' => $item->ID]),
                ],
              ],
            ],
          ],
        ];
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
    }

    if (!empty($rows)) {
      $build = [
        '#type' => 'table',
        '#caption' => $this->t('Repository Synchronization settings overview.'),
        '#header' => [$this->t('Name'), $this->t('UUID'), $this->t('Owner'), $this->t('Operations')],
        '#rows' => $rows,
        '#description' => $this->t('Repository Synchronization settings overview.'),
      ];
    }
    return $build ?? NULL;
  }

}
