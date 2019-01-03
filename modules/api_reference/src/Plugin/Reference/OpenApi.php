<?php

namespace Drupal\devportal_api_reference\Plugin\Reference;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\devportal_api_reference\Plugin\OpenApiValidationException;
use JsonSchema\Validator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for OpenAPI references.
 */
abstract class OpenApi extends ReferenceBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Cache\CacheBackendInterface $cache */
    $cache = $container->get('cache.apifiles');
    /** @var \Drupal\Core\Logger\LoggerChannelInterface $logger */
    $logger = $container->get('logger.channel.api_reference');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $cache,
      $logger
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, CacheBackendInterface $cache, LoggerChannelInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cache = $cache;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function getVersion(?\stdClass $doc): ?string {
    if (!$doc) {
      return NULL;
    }

    return $doc->info->version ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(?\stdClass $doc): ?string {
    if (!$doc) {
      return NULL;
    }

    return $doc->info->title ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(?\stdClass $doc): ?string {
    if (!$doc) {
      return NULL;
    }

    return $doc->info->description ?? NULL;
  }

  /**
   * Path to the JSON schema file.
   *
   * @return string
   *   Path relative to Drupal.
   */
  abstract protected function getSchema(): string;

  /**
   * Checks if an OpenAPI file is valid.
   *
   * Normally, plugins should check the version in data structure. This
   * function is used to determine if the current plugin is applicable to be
   * used for a given file. Since different OpenAPI versions use the same
   * formats (YAML and JSON), this function is need to tell which one it is.
   *
   * @param \stdClass $data
   *   OpenAPI data structure.
   *
   * @return bool
   *   TRUE if valid.
   */
  abstract protected function isValid(\stdClass $data): bool;

  /**
   * {@inheritdoc}
   */
  public function parse(string $file_path): ?\stdClass {
    $cid = $file_path . ':' . md5_file($file_path);
    $cached = $this->cache->get($cid);
    if ($cached) {
      if (($cached->data['plugin'] ?? NULL) === $this->getPluginId()) {
        return $cached->data['object'] ?? NULL;
      }
      return NULL;
    }

    if (!file_exists($file_path)) {
      $this->logger->warning("File doesn't exists: {$file_path}");
      return NULL;
    }

    $file_info = pathinfo($file_path);
    $file_ext = $file_info['extension'];

    $input = file_get_contents($file_path);
    if (($file_ext === 'yaml') || ($file_ext === 'yml')) {
      try {
        $openapi = Yaml::parse($input, Yaml::PARSE_OBJECT | Yaml::PARSE_OBJECT_FOR_MAP);
      }
      catch (ParseException $e) {
        throw new \Exception("Can not parse YAML source file ({$file_path}). {$e->getMessage()}", 0, $e);
      }
    }
    elseif ($file_ext === 'json') {
      $openapi = json_decode($input, FALSE);
      if ($openapi === NULL) {
        throw new \Exception("The JSON source file ({$file_path}) cannot be decoded (possible syntax error) or the encoded data is deeper then the recursion limit (512).");
      }
    }
    else {
      throw new \Exception("Unsupported source file extension: {$file_ext}. Please use YAML or JSON source.");
    }

    if (!$this->isValid($openapi)) {
      return NULL;
    }

    $this->validate($openapi);

    $this->cache->set($cid, [
      'object' => $openapi,
      'plugin' => $this->getPluginId(),
    ], Cache::PERMANENT);

    return $openapi;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(\stdClass $content) {
    $validator = new Validator();
    $validator->validate($content, (object) [
      '$ref' => 'file://' . ($_SERVER['DOCUMENT_ROOT'] ?: getcwd()) . '/' . $this->getSchema(),
    ]);
    if (!$validator->isValid()) {
      $errors = $validator->getErrors();
      throw OpenApiValidationException::fromErrors($errors);
    }
  }

}
