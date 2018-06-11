<?php

namespace Drupal\devportal_repo_sync\Exception;

class DevportalRepoSyncConnectionException extends \Exception {

  public function __construct($message) {
    parent::__construct($message);
  }

}
