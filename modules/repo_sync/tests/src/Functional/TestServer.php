<?php

namespace Drupal\Tests\devportal_repo_sync\Functional;

/**
 * Runs a background HTTP server.
 */
class TestServer {

  /**
   * The server's listen address.
   *
   * @var string
   */
  protected $serverAddr;

  /**
   * Array of pipes of the server process.
   *
   * @var array
   */
  protected $pipes = [];

  /**
   * The running server's process resource.
   *
   * @var resource|null
   */
  protected $process = NULL;

  /**
   * TestServer constructor.
   *
   * @param string $addr
   *   Server addr to listen on.
   */
  public function __construct(string $addr) {
    $this->serverAddr = $addr;
  }

  /**
   * Starts the test server.
   */
  public function start() {
    $filename = escapeshellarg(dirname(__FILE__) . '/../../fixtures/server.php');
    $cmd = "php -S {$this->serverAddr} {$filename}";
    $env = NULL;
    $options = ['bypass_shell' => TRUE];
    $cwd = NULL;
    $descriptorspec = [
      // stdin.
      0 => ['pipe', 'r'],
      // stdout.
      1 => ['pipe', 'w'],
      // stderr.
      2 => ['pipe', 'w'],
    ];

    $this->process = proc_open($cmd, $descriptorspec, $this->pipes, $cwd, $env, $options);
    if (!$this->process) {
      throw new \Exception('Failed to start server');
    }
    foreach ($this->pipes as $pipe) {
      stream_set_blocking($pipe, 0);
    }
  }

  /**
   * Waits for a response.
   *
   * @param int $seconds
   *   Seconds to wait for the response.
   *
   * @return array
   *   Response HTTP array. See server.php for the structure.
   */
  public function wait(int $seconds = 30): array {
    do {
      sleep(1);
      $seconds--;
      if ($seconds === 0) {
        throw new \Exception('timeout');
      }
      $response = stream_get_contents($this->pipes[2]);
    } while (!$response);

    return json_decode($response, TRUE);
  }

  /**
   * Stops the server.
   */
  public function stop() {
    if ($this->process !== NULL) {
      array_map('fclose', $this->pipes);
      proc_terminate($this->process, 9);
      proc_close($this->process);
      $this->process = NULL;
      $this->pipes = [];
    }
  }

  /**
   * Destructor.
   */
  public function __destruct() {
    $this->stop();
  }

}
