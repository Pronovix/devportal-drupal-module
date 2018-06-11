<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class RepoSyncCallbackController.
 */
class RepoSyncCallbackController extends ControllerBase {

  /**
   * Callback.
   *
   * @param $uuid
   *   The UUID of the repository import.
   * @param $hash
   *   The callback path parameter.
   *
   * @return array
   *   Return callback array.
   */
  public function callback(string $uuid, string $hash) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t("This is your UUID: $uuid, and this is your hash: $hash"),
    ];
  }

}
