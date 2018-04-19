<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\devportal_repo_sync\Entity\RepoAccount;
use Drupal\devportal_repo_sync\Entity\RepoImport;
use Drupal\devportal_migrate_batch\Plugin\QueueWorker\ImportQueueWorker;
use Drupal\devportal_repo_sync\RepoSourceInfoTrait;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for the webhook-related pages.
 */
class RepoImportWebhookController extends ControllerBase {

  use RepoSourceInfoTrait;
  use ContainerAwareTrait;

  /**
   * HTTP client.
   *
   * @var Client
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(MigrateSourcePluginManager $migrateSourcePluginManager, Client $client) {
    $this->migrateSourcePluginManager = $migrateSourcePluginManager;
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var MigrateSourcePluginManager $source_plugin_manager */
    $source_plugin_manager = $container->get('plugin.manager.migrate.source');

    /** @var Client $http_client */
    $http_client = $container->get('http_client');

    $instance = new static($source_plugin_manager, $http_client);
    $instance->setContainer($container);

    return $instance;
  }

  /**
   * Page callback for the 'entity.repo_import.webhook' route.
   *
   * @param RepoImport $repo_import
   * @param string $repo_import_webhook
   * @param Request $request
   *
   * @return Response
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function ping(RepoImport $repo_import, $repo_import_webhook, Request $request) {
    if (!$repo_import->webhook || $repo_import->webhook !== $repo_import_webhook) {
      return new Response('', Response::HTTP_FORBIDDEN);
    }

    $repo_account = RepoAccount::load($repo_import->repo_account_id);

    $provider_class = $this->mapRepositoryProviders(function ($definition) {
      return $definition['class'];
    })[$repo_account->getProvider()];

    $payload = Json::decode($request->getContent());
    // Put the file into a cron queue for later processing.
    if ($payload && call_user_func([$provider_class, 'filterWebhook'], $repo_import->version, $payload)) {
      $instance = $this->createProviderInstance($repo_account, $repo_import->repository, $repo_import->version);
      $instance->resetCache();
      ImportQueueWorker::createItem($repo_import, $this->container);

      if (($ping = $this->config('devportal_repo_sync.import')->get('webhook.ping'))) {
        try {
          $this->client->get($ping);
        }
        catch (\Exception $e) {
          watchdog_exception('devportal_repo_sync_webhook', $e);
        }
      }
    }

    return new Response('', Response::HTTP_ACCEPTED);
  }

}
