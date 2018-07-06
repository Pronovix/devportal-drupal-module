<?php

namespace Drupal\devportal_repo_sync\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Connector service for the repository importer.
 */
class RepoSyncConnector {

  /**
   * Repository importer client instance.
   *
   * @var \Drupal\devportal_repo_sync\Service\Client
   */
  protected $client;

  /**
   * Callback url base.
   *
   * If empty, Drupal's default will be used.
   *
   * @var string
   */
  protected $baseurl;

  /**
   * RepoSyncConnector constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config) {
    $c = $config->get('devportal_repo_sync.config');
    $account = $c->get('account');
    $secret = $c->get('secret');
    $service = $c->get('service');
    if ($account && $secret && $service) {
      $this->baseurl = $c->get('baseurl');
      $this->client = new Client($account, hex2bin($secret), $service);
    }
  }

  /**
   * Sends a request to the repository importer service.
   *
   * @param string $method
   *   HTTP method.
   * @param string $endpoint
   *   Endpoint name.
   * @param array|null $body
   *   Data to be sent, or NULL if there is no body.
   *
   * @return array|null
   *   Endpoint result.
   *
   * @throws \Exception
   *   An exception will be thrown if the service's return code is >= 400.
   */
  protected function request(string $method, string $endpoint, ?array $body = NULL) {
    $client = $this->client;
    if (!$client) {
      throw new HttpException(Response::HTTP_NOT_FOUND);
    }

    $result = $client($method, $endpoint, json_encode($body));

    if ($result[0] >= 400) {
      throw new HttpException($result[0], $result[1]);
    }

    return $result ? json_decode(array_pop($result), TRUE) : NULL;
  }

  /**
   * Returns all imports for the given user.
   *
   * @return array
   *   List of imports.
   */
  public function getImports() {
    return $this->request('GET', '/api/import', NULL);
  }

  /**
   * Returns a given import.
   *
   * @param string $uuid
   *   Import id.
   *
   * @return array
   *   Import data.
   */
  public function getImport(string $uuid) {
    return $this->request('GET', "/api/import/{$uuid}", NULL);
  }

  /**
   * Creates an import.
   *
   * @param string $label
   *   Label of the import.
   * @param string $repository_url
   *   Repository URL of the import.
   * @param string $pattern
   *   File matching pattern of the import.
   * @param string $reference
   *   Repository reference of the import.
   * @param string $base_path
   *   Base path of the import.
   *
   * @return array
   *   The created import.
   */
  public function createImport(string $label, string $repository_url, string $pattern, string $reference, string $base_path) {
    $result = $this->request('POST', '/api/import', [
      "Label" => $label,
      "RepositoryType" => 'git',
      "RepositoryURL" => $repository_url,
      "Pattern" => $pattern,
      "Reference" => $reference,
      "BasePath" => $base_path,
    ]);
    return $this->updateImport($result);
  }

  /**
   * Updates an import.
   *
   * @param array $body
   *   The updated import's body.
   *
   * @return array
   *   The updated import.
   */
  public function updateImport(array $body) {
    $body['Callback'] = $this->createCallback($body['ID']);
    return $this->request('PUT', "/api/import/{$body["ID"]}", $body);
  }

  /**
   * Deletes an import.
   *
   * @param string $uuid
   *   UUID of the import to be deleted.
   */
  public function deleteImport(string $uuid) {
    $this->request('DELETE', "/api/import/{$uuid}");
  }

  /**
   * Triggers an import run.
   *
   * @param string $uuid
   *   UUID of the import to trigger.
   */
  public function run(string $uuid) {
    $this->request(Request::METHOD_POST, "/api/import/{$uuid}/run", NULL);
  }

  /**
   * Creates a hash for a given data.
   *
   * This function is used to generate a hash for the callback url. This way,
   * it is not needed to store data locally for an import.
   *
   * @param string $data
   *   Data to generated the hash from.
   *
   * @return string
   *   The generated hash.
   */
  public function createHash(string $data): string {
    return hash_hmac('sha256', $data, Settings::getHashSalt(), FALSE);
  }

  /**
   * Creates a callback URL.
   *
   * @param string $uuid
   *   Import UUID to create the callback URL for.
   *
   * @return string
   *   The generated callback URL.
   */
  public function createCallback(string $uuid): string {
    $url = Url::fromRoute('devportal_repo_sync.controller_callback', [
      'uuid' => $uuid,
      'hash' => $this->createHash($uuid),
    ]);

    if ($this->baseurl) {
      return $this->baseurl . $url->toString();
    }
    else {
      $url->setAbsolute(TRUE);
    }

    return $url->toString();
  }

}
