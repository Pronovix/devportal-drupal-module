<?php

namespace Drupal\devportal_repo_sync\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RepoSyncConnector {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * @var \Drupal\devportal_repo_sync\Service\Client
   */
  protected $client;

  /**
   * RepoSyncConnector constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->config = $config;
  }

  /**
   * @param string $method
   * @param string $endpoint
   * @param string $uuid
   * @param array|NULL $body
   *
   * @return array|bool|mixed
   *
   * @throws \Exception
   */
  protected function request(string $method, string $endpoint, string $uuid = NULL, array $body = NULL) {
    $client = $this->getClient();

    if ($uuid) {
      $endpoint .= '/' . $uuid;
    }

    $result = $client($method, $endpoint, json_encode($body));

    if ($result[0] >= 400) {
      throw new HttpException($result[0], $result[1]);
    }

    return json_decode(array_pop($result), TRUE);
  }

  /**
   * @return array
   */
  protected function getAllFromConfig() {
    $config = $this->config->get('devportal_repo_sync.config');

    $account = $config->get('account');
    $secret = $config->get('secret');
    $service = $config->get('service');

    return [$account, $secret, $service];
  }

  /**
   * @return \Drupal\devportal_repo_sync\Service\Client
   */
  protected function getClient() {
    if ($this->client) {
      return $this->client;
    }
    else {
      list($account, $secret, $service) = $this->getAllFromConfig();
      $this->client = new Client($account, hex2bin($secret), $service);
      return $this->client;
    }
  }

  /**
   * @return array|bool|mixed
   *
   * @throws \Exception
   */
  public function getImports() {
    return $this->request('GET', '/api/import', NULL, NULL);
  }

  /**
   * @param string $uuid
   *
   * @return array|bool|mixed
   *
   * @throws \Exception
   */
  public function getImport(string $uuid) {
    return $this->request('GET', '/api/import', $uuid, NULL);
  }

  /**
   * @param string $label
   * @param string $repository_url
   * @param string $pattern
   * @param string $reference
   *
   * @return array|bool|mixed
   *
   * @throws \Exception
   */
  public function createImport(string $label, string $repository_url, string $pattern, string $reference) {
    $result = $this->request('POST', '/api/import', NULL, [
      "Label" => $label,
      "RepositoryType" => 'git',
      "RepositoryURL" => $repository_url,
      "Pattern" => $pattern,
      "Reference" => $reference,
    ]);
    $result['Callback'] = Url::fromRoute('devportal_repo_sync.controller_callback', [
      'uuid' => $result["ID"],
      'hash' => hash_hmac('sha256', $result["ID"], Settings::getHashSalt(), FALSE),
    ], ['absolute' => TRUE])->toString();
    return $this->updateImport($result);
  }

  /**
   * @param array $body
   *
   * @return array|bool|mixed
   *
   * @throws \Exception
   */
  public function updateImport(array $body) {
    return $this->request('PUT', '/api/import', $body["ID"], $body);
  }

  /**
   * @param string $uuid
   *
   * @return array|bool|mixed
   *
   * @throws \Exception
   */
  public function deleteImport(string $uuid) {
    return $this->request('DELETE', '/api/import', $uuid);
  }
}
