<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\devportal_repo_sync\Exception\DevportalRepoSyncConnectionException;
use Drupal\devportal_repo_sync\Service\Client;

/**
 * Class RepoSyncViewController.
 */
class RepoSyncViewController extends ControllerBase {

  /**
   * Hello.
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
    $config = $this->config('devportal_repo_sync.config');
    $client = new Client($config->get('uuid'), hex2bin($config->get('secret')), $config->get('service'));

    try {
      $result = $client("GET", "/api/import/$uuid", NULL);
      if ($result[0] == 200) {
        $result = json_decode(array_pop($result), TRUE);

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
      }
      else {
        $build = self::error($result[1]);
      }
    }
    catch (DevportalRepoSyncConnectionException $e) {
      $this->messenger()->addError($e->getMessage());
      watchdog_exception('repo_sync', $e);
      $build = self::error($e->getMessage());
    }

    return $build;
  }

  public static function error($message) {
    return [
      '#type' => 'markup',
      '#markup' => t("An error occurred: %message", ['%message' => $message]),
    ];
  }

}
