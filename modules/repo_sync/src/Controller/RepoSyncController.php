<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Url;
use Drupal\devportal_repo_sync\Service\RepoSyncConnector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class RepoSyncController.
 */
class RepoSyncController extends ControllerBase {

  /**
   * @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector
   */
  protected $devportalRepoSyncConnection;

  /**
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\devportal_repo_sync\Service\RepoSyncConnector $connector */
    $connector = $container->get('devportal_repo_sync.connection');

    /** @var \Drupal\Core\Queue\QueueFactory $queueFactory */
    $queueFactory = $container->get('queue');

    return new static(
      $connector,
      $queueFactory->get('file_import')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(RepoSyncConnector $connector, QueueInterface $queue) {
    $this->devportalRepoSyncConnection = $connector;
    $this->queue = $queue;
  }

  /**
   * Callback endpoint for the repo importer service.
   *
   * @param string $uuid
   *   The UUID of the repository import.
   * @param string $hash
   *   The callback path parameter.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Return callback array.
   */
  public function callback(string $uuid, string $hash): Response {
    if (!hash_equals($this->devportalRepoSyncConnection->createHash($uuid), $hash)) {
      throw new AccessDeniedHttpException("hashes don't match");
    }

    $data = json_decode(file_get_contents('php://input'), TRUE);
    foreach ($data['Files'] as $filename => $file) {
      $original = $file['Files']['Original']['URL'] ?? NULL;
      $processed = $file['Files']['Processed']['URL'] ?? NULL;

      $this->queue->createItem((object) [
        'import' => $uuid,
        'filename' => $filename,
        'original' => $original,
        'processed' => $processed,
      ]);
    }

    return Response::create('', Response::HTTP_ACCEPTED);
  }

  /**
   * Triggers an import.
   *
   * @param string $uuid
   *   The UUID of the import.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Redirects to the import's view page.
   */
  public function trigger(string $uuid): Response {
    $this->devportalRepoSyncConnection->run($uuid);

    $this->messenger()->addStatus($this->t('The import has been started.'));

    return new RedirectResponse(Url::fromRoute('devportal_repo_sync.controller_view', [
      'uuid' => $uuid,
    ])->toString(), Response::HTTP_SEE_OTHER);
  }

}
