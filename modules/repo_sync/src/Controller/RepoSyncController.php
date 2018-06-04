<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\devportal_repo_sync\Service\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RepoSyncController.
 */
class RepoSyncController extends ControllerBase {

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new RepoSyncController object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

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

      $links = [];
      $links['view'] = [
        'title' => $this->t('View'),
        'url' => Url::fromRoute('devportal_repo_sync.devportal_repo_sync_config_form'),
      ];
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('devportal_repo_sync.devportal_repo_sync_create_form'),
      ];
      $operations = [
        'data' => [
          '#type' => 'operations',
          '#links' => $links,
        ]
      ];

      foreach ($result->items as $item) {
        $rows[] = [$item->Label, $item->ID, $item->Owner, $operations];
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
    }

    if (!empty($rows)) {
      $build = [
        '#type' => 'table',
        '#caption' => $this->t('Repository Synchronization settings overview.'),
        '#header' => [$this->t('Name'), $this->t('ID'), $this->t('Owner'), $this->t('Operations')],
        '#rows' => $rows,
        '#description' => $this->t('Repository Synchronization settings overview.'),
      ];
    }
    return $build ?? NULL;
  }

}
