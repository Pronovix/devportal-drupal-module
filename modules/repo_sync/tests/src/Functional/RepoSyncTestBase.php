<?php

namespace Drupal\Tests\devportal_repo_sync\Functional;

use Drupal\Tests\devportal\Functional\DevportalTestBase;

/**
 * A base class for repository sync functional tests.
 */
abstract class RepoSyncTestBase extends DevportalTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'devportal_repo_sync',
    'block',
  ];

  /**
   * List of import UUIDs that needs to be clean up after the tests.
   *
   * @var string[]
   */
  protected $cleanUp = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $account = getenv('DEVPORTAL_REPO_SYNC_ACCOUNT');
    $secret = getenv('DEVPORTAL_REPO_SYNC_SECRET');
    $service = getenv('DEVPORTAL_REPO_SYNC_SERVICE');
    $baseurl = getenv('DEVPORTAL_REPO_SYNC_BASEURL');
    if (!$account || !$secret || !$service) {
      $this->markTestIncomplete('Environment variables are not configured properly. Make sure you set DEVPORTAL_REPO_SYNC_{ACCOUNT,SECRET,SERVICE}.');
    }

    parent::setUp();

    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');

    \Drupal::configFactory()->getEditable('devportal_repo_sync.config')
      ->set('account', $account)
      ->set('secret', $secret)
      ->set('service', $service)
      ->set('baseurl', $baseurl)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    /** @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector $connection */
    $connection = \Drupal::service('devportal_repo_sync.connection');
    foreach ($this->cleanUp as $id) {
      try {
        $connection->deleteImport($id);
      }
      catch (\Exception $e) {
      }
    }

    parent::tearDown();
  }

  /**
   * Returns the UUID from the current URL.
   *
   * @return string
   *   UUID in the standard 8-4-4-4-12 format.
   */
  protected function getUuid(): string {
    $url = $this->getSession()->getCurrentUrl();
    $matches = [];
    if (preg_match('/[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}/i', $url, $matches)) {
      return $matches[0];
    }

    return '';
  }

}
