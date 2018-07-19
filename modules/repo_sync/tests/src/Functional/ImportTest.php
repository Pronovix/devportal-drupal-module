<?php

namespace Drupal\Tests\devportal_repo_sync\Functional;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * File import related tests.
 *
 * @group devportal
 * @group repo_sync
 */
class ImportTest extends RepoSyncTestBase {

  /**
   * Address of the background HTTP server.
   *
   * @var string
   */
  public $serverAddr = '0.0.0.0:9999';

  /**
   * Import CRUD test.
   */
  public function testCrud() {
    $this->drupalLogin($this->rootUser);
    $label = $this->getRandomGenerator()->name();
    $repo_url = 'https://github.com/tamasd/git-test.git';
    $base_path = '/content/import/test';
    $pattern = 'docs/';
    $reference = 'refs/heads/master';

    // Create.
    $this->drupalPostForm(Url::fromRoute('devportal_repo_sync.create_form'), [
      'label' => $label,
      'repository_url' => $repo_url,
      'pattern' => $pattern,
      'reference' => $reference,
      'base_path' => $base_path,
    ], 'Save');
    $uuid = $this->getUuid();
    $this->assertNotEmpty($uuid);
    $this->cleanUp[] = $uuid;

    // View.
    $this->assertSession()->pageTextContains($label);
    $this->assertSession()->pageTextContains($repo_url);
    $this->assertSession()->pageTextContains($base_path);
    $this->assertSession()->pageTextContains($pattern);
    $this->assertSession()->pageTextContains($reference);

    // List.
    $this->drupalGet(Url::fromRoute('devportal_repo_sync.controller_content'));
    $this->assertSession()->pageTextContains($label);
    $this->clickLink($label);

    // Update.
    $this->clickLink('Edit');
    $new_label = $this->getRandomGenerator()->name();
    $this->drupalPostForm(NULL, [
      'label' => $new_label,
    ], 'Save');
    $this->assertSession()->pageTextNotContains($label);
    $this->assertSession()->pageTextContains($new_label);
    $this->assertSession()->pageTextContains($repo_url);
    $this->assertSession()->pageTextContains($base_path);
    $this->assertSession()->pageTextContains($pattern);
    $this->assertSession()->pageTextContains($reference);

    // Delete.
    $this->clickLink('Delete');
    $this->drupalPostForm(NULL, [], 'Confirm');

    // List.
    $this->assertSession()->pageTextNotContains($label);
    $this->assertSession()->pageTextNotContains($new_label);
  }

  /**
   * Tests the import process.
   */
  public function testImport() {
    $this->drupalLogin($this->rootUser);
    $label = $this->getRandomGenerator()->name();
    $repo_url = 'https://github.com/tamasd/git-test.git';
    $base_path = '/content/import/test';
    $pattern = 'docs/';
    $reference = 'refs/heads/master';

    // Create.
    $this->drupalPostForm(Url::fromRoute('devportal_repo_sync.create_form'), [
      'label' => $label,
      'repository_url' => $repo_url,
      'pattern' => $pattern,
      'reference' => $reference,
      'base_path' => $base_path,
    ], 'Save');
    $uuid = $this->getUuid();
    $this->assertNotEmpty($uuid);
    $this->cleanUp[] = $uuid;

    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $installer */
    $installer = $this->container->get('module_installer');
    $installer->install(['devportal_api_reference']);

    $server = new TestServer($this->serverAddr);
    try {
      $server->start();

      $this->clickLink('Start import');
      $this->assertSession()->pageTextContains('The import has been started.');

      $response = $server->wait();
      $this->assertNotEmpty($response);
    }
    finally {
      $server->stop();
    }

    $this->post(
      $response['uri'],
      $response['body'],
      $response['headers']['Content-Type'],
      Response::HTTP_ACCEPTED
    );

    $this->drainQueue('file_import');

    $nodes = $this->container->get('entity_type.manager')->getStorage('node')->loadMultiple();
    $this->assertGreaterThan(0, count($nodes));

    $titles = array_map(function (NodeInterface $node) {
      return $node->getTitle();
    }, $nodes);

    $expected = [
      'docs/bar/bar.md',
      'docs/bar/zxcvbn/zxcvbn.md',
      'docs/foo/foo.md',
      'docs/foo/qwerty/qwerty.md',
      'docs index',
      'NEW',
      'Swagger Petstore',
    ];

    foreach ($expected as $title) {
      $this->assertContains($title, $titles);
    }
  }

}
