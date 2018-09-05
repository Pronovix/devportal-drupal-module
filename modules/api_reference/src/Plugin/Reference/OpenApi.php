<?php

namespace Drupal\devportal_api_reference\Plugin\Reference;

use Drupal\Core\Cache\Cache;
use Drupal\devportal_api_reference\Plugin\OpenApiValidationException;
use JsonSchema\Validator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for OpenAPI references.
 */
abstract class OpenApi extends ReferenceBase {

  /**
   * {@inheritdoc}
   */
  public function getVersion(?object $doc): ?string {
    if (!$doc) {
      return NULL;
    }

    return $doc->info->version ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(?object $doc): ?string {
    if (!$doc) {
      return NULL;
    }

    return $doc->info->title ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(?object $doc): ?string {
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
   * @param object $data
   *   OpenAPI data structure.
   *
   * @return bool
   *   TRUE if valid.
   */
  abstract protected function isValid(object $data): bool;

  /**
   * {@inheritdoc}
   */
  public function parse(string $file_path): ?object {
    $bin = \Drupal::cache('apifiles');
    $cid = $file_path . ':' . md5_file($file_path);
    $cached = $bin->get($cid);
    if ($cached) {
      return $cached->data;
    }

    $file_info = pathinfo($file_path);
    $file_ext = $file_info['extension'];

    if (($file_ext === 'yaml') || ($file_ext === 'yml')) {
      try {
        $openapi = Yaml::parse(file_get_contents($file_path), Yaml::PARSE_OBJECT | Yaml::PARSE_OBJECT_FOR_MAP);
      }
      catch (ParseException $e) {
        throw new \Exception("Can not parse YAML source file ({$file_path}).");
      }
    }
    elseif ($file_ext === 'json') {
      $openapi = json_decode(file_get_contents($file_path), FALSE);
      if ($openapi === NULL) {
        throw new \Exception("The JSON source file ({$file_path}) cannot be decoded or the encoded data is deeper then the recursion limit (512).");
      }
    }
    else {
      throw new \Exception("Unsupported source file extension: {$file_ext}. Please use YAML or JSON source.");
    }

    if (!$this->isValid($openapi)) {
      return NULL;
    }

    $this->validate($openapi);

    $bin->set($cid, $openapi, Cache::PERMANENT);

    return $openapi;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(object $content) {
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
