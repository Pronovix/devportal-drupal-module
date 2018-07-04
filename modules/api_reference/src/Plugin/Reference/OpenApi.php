<?php

namespace Drupal\devportal_api_reference\Plugin\Reference;

use Drupal\Core\Cache\Cache;
use Drupal\devportal_api_reference\Plugin\OpenApiValidationException;
use JsonSchema\Validator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

abstract class OpenApi extends ReferenceBase {

  public function getVersion(string $path): ?string {
    if (!$path) {
      return NULL;
    }

    return $this->parse($path)['info']['version'] ?? NULL;
  }

  public function getTitle(string $path): ?string {
    if (!$path) {
      return NULL;
    }

    return $this->parse($path)['info']['title'] ?? NULL;
  }

  public function getDescription(string $path): ?string {
    if (!$path) {
      return NULL;
    }

    return $this->parse($path)['info']['description'] ?? NULL;
  }

  abstract protected function getSchema(): string;

  abstract protected function isValid(array $data): bool;

  public function parse(string $file_path): ?array {
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
        $openapi = Yaml::parse(file_get_contents($file_path));
      }
      catch (ParseException $e) {
        throw new \Exception("Can not parse YAML source file ({$file_path}).");
      }
    }
    elseif ($file_ext === 'json') {
      $openapi = json_decode(file_get_contents($file_path), TRUE);
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

  public function validate(array $content) {
    // Converts the content associative array into objects.
    $objectified = json_decode(json_encode($content));
    $validator = new Validator();
    $validator->validate($objectified, (object) [
      '$ref' => 'file://' . ($_SERVER['DOCUMENT_ROOT'] ?: getcwd()) . '/' . $this->getSchema(),
    ]);
    if (!$validator->isValid()) {
      $errors = $validator->getErrors();
      throw OpenApiValidationException::fromErrors($errors);
    }
  }

}
