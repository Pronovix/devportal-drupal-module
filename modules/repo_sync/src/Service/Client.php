<?php

declare(strict_types = 1);

namespace Drupal\devportal_repo_sync\Service;

class Client {

  /**
   * Binary representation of the secret.
   *
   * @var string
   */
  protected $secret;

  /**
   * Binary representation of the nonce.
   *
   * @var string
   */
  protected $nonce;

  /**
   * @var string
   */
  protected $host;

  /**
   * @var string
   */
  protected $userid;

  /**
   * @var string
   */
  private $jarfile;

  /**
   * @var string
   */
  protected $token;

  /**
   * @return string
   */
  public function getUserid(): string {
    return $this->userid;
  }

  /**
   * @return mixed
   */
  public function getHost(): string {
    return $this->host;
  }

  /**
   * Client constructor.
   *
   * @param string $userid
   * @param string $secret
   * @param string $host
   */
  public function __construct(string $userid, string $secret, string $host) {
    $this->userid = $userid;
    $this->secret = $secret;
    $this->host = $host;
    $this->jarfile = tempnam("", "");
  }

  /**
   * @param string $method
   * @param string $path
   * @param null|string $body
   *
   * @return array
   *
   * @throws \Exception
   */
  public function __invoke(string $method, string $path, ?string $body): array {
    if (($tokenresponse = $this->ensureToken())) {
      return $tokenresponse;
    }
    $this->refreshNonce();

    // Send the actual request.
    $headers = [
      "Authorization: " . $this->createHeader($this->signRequest($method, $path, $body)),
      "X-CSRF-Token: {$this->token}",
    ];

    if ($body !== null) {
      $headers[] = "Content-Type: application/json";
    }

    $this->executed = true;

    return $this->withCurl($method, $this->host . $path, $headers, function($ch) use($body) {
      if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
      }
    });
  }

  /**
   * Removes the jarfile.
   */
  public function __destruct() {
    @unlink($this->jarfile);
  }

  /**
   * @return array|null
   *
   * @throws \Exception
   */
  protected function ensureToken(): ?array {
    if ($this->token !== null) {
      return null;
    }

    // Retrieve the CSRF Token.
    list($code, $result) = $this->withCurl("GET", $this->host . "/api/token", [
      "Accept: application/json",
    ]);
    if ($code !== 200) {
      return [$code, $result];
    }
    $this->token = json_decode($result, true)["token"];

    return null;
  }

  /**
   * Generates a new nonce.
   */
  protected function refreshNonce() {
    $this->nonce = random_bytes(64);
  }

  /**
   * @param string $method
   * @param string $path
   * @param null|string $body
   *
   * @return string
   */
  protected function signRequest(string $method, string $path, ?string $body): string {
    $now = (int) (round(time() / 60) * 60);
    $nowstr = gmdate("Y-m-d\\TH:i:s\\Z", $now);
    $data = "{$this->nonce}\n{$nowstr}\n{$method} {$path}\n{$body}";

    return hash_hmac("sha256", $data, $this->secret);
  }

  /**
   * @param string $signature
   *
   * @return string
   */
  protected function createHeader(string $signature): string {
    $noncehex = bin2hex($this->nonce);
    return "Bearer {$this->userid}:{$noncehex}:{$signature}";
  }

  /**
   * @param string $method
   * @param string $url
   * @param array $headers
   * @param callable|null $callback
   *
   * @return array
   * @throws \Exception
   */
  protected function withCurl(string $method, string $url, array $headers, ?callable $callback = null): array {
    $ch = curl_init();

    curl_setopt_array($ch, [
      CURLOPT_AUTOREFERER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SAFE_UPLOAD => true,
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_HEADER => true,
      CURLOPT_USERAGENT => "Repo Import Example Client",
      CURLOPT_COOKIEJAR => $this->jarfile,
      CURLOPT_COOKIEFILE => $this->jarfile,
    ]);

    if ($callback !== null) {
      $callback($ch);
    }

    if ($rawresult = curl_exec($ch)) {
      $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $result = substr($rawresult, $header_size);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    else {
      throw new \Exception('Curl handler error.');
    }
    curl_close($ch);
    return [$code, $result];
  }

}
