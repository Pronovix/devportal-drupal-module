<?php

namespace Drupal\Tests\devportal\Functional;

use Drupal\Core\Queue\RequeueException;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Listeners\DeprecationListenerTrait;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SetCookie;

/**
 * A function test base class for various devportal tests.
 */
abstract class DevportalTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'devportal',
  ];

  /**
   * Sends a POST request to Drupal.
   *
   * @param string $path
   *   Request path.
   * @param string $body
   *   Request body.
   * @param string $content_type
   *   Request content type.
   * @param int $expected_response_code
   *   The expected response code from Drupal.
   * @param array $options
   *   Request URL options.
   *
   * @return string
   *   Response body.
   */
  protected function post(string $path, string $body, string $content_type, int $expected_response_code, array $options = []): string {
    $options['absolute'] = TRUE;
    $url = $this->buildUrl($path, $options);
    $requested_token = $this->drupalGet('session/token');
    $this->assertNotEmpty($requested_token);
    $client = $this->container->get('http_client');

    $client_options = [
      'http_errors' => FALSE,
      'cookies' => $this->cookies(),
      'curl' => [
        CURLOPT_HEADERFUNCTION => [&$this, 'curlHeaderCallback'],
      ],
      'headers' => [
        'Content-Type' => $content_type,
        'X-CSRF-Token' => $requested_token,
      ],
      'body' => $body,
    ];

    /** @var \Psr\Http\Message\ResponseInterface $response */
    $response = $client->post($url, $client_options);

    $this->assertEquals($expected_response_code, $response->getStatusCode());

    return (string) $response->getBody()->getContents();
  }

  /**
   * Generates a set of default cookies for post().
   *
   * @return \GuzzleHttp\Cookie\CookieJarInterface
   *   The assembled cookie jar.
   */
  protected function cookies(): CookieJarInterface {
    $request = $this->container->get('request_stack')->getCurrentRequest();
    $cookies = $this->extractCookiesFromRequest($request);
    $jar = new FileCookieJar($this->publicFilesDirectory . '/cookie.jar');

    foreach ($cookies as $key => $values) {
      foreach ($values as $value) {
        if ($value !== 'deleted') {
          $jar->setCookie(new SetCookie([
            'Name' => $key,
            'Value' => $value,
            'Domain' => $request->getHost(),
          ]));
        }
      }
    }

    return $jar;
  }

  /**
   * Curl header callback function.
   *
   * @param resource $curlHeader
   *   Curl resource.
   * @param string $header
   *   Header string.
   *
   * @return int
   *   Header length.
   */
  public function curlHeaderCallback($curlHeader, string $header) {
    if (preg_match('/^X-Drupal-Assertion-[0-9]+: (.*)$/', $header, $matches)) {
      $parameters = unserialize(urldecode($matches[1]));

      if ($parameters[1] === 'User deprecated function') {
        if (getenv('SYMFONY_DEPRECATIONS_HELPER') !== 'disabled') {
          $message = (string) $parameters[0];
          if (!in_array($message, DeprecationListenerTrait::getSkippedDeprecations())) {
            call_user_func_array([&$this, 'error'], $parameters);
          }
        }
      }
      else {
        call_user_func_array([&$this, 'error'], $parameters);
      }
    }

    return strlen($header);
  }

  /**
   * Makes sure that a given queue has items in it, and processes them.
   *
   * @param string $name
   *   Queue name.
   */
  protected function drainQueue(string $name) {
    $queue = $this->container->get('queue')->get($name);
    $this->assertGreaterThan(0, $queue->numberOfItems());

    /** @var \Drupal\Core\Queue\QueueWorkerManager $manager */
    $manager = $this->container->get('plugin.manager.queue_worker');
    $worker = $manager->createInstance($name);
    while (($item = $queue->claimItem())) {
      try {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
      catch (RequeueException $e) {
        $queue->releaseItem($item);
      }
      catch (SuspendQueueException $e) {
        $queue->releaseItem($item);
        throw new \Exception($e->getMessage(), $e->getCode(), $e);
      }
    }

    $this->assertEquals(0, $queue->numberOfItems());
  }

  /**
   * Installs modules and rebuilds the cache.
   *
   * @param string[] $module_list
   *   List of modules.
   *
   * @see \Drupal\Tests\toolbar\Functional\ToolbarCacheContextsTest::installExtraModules()
   */
  protected function installExtraModules(array $module_list) {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $installer */
    $installer = $this->container->get('module_installer');
    $installer->install($module_list);

    $this->container->get('router.builder')->rebuildIfNeeded();
  }

}
