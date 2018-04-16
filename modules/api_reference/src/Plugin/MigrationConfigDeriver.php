<?php

namespace Drupal\devportal_api_reference\Plugin;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Cache\Cache;
use Drupal\devportal_api_reference\Entity\APIRef;
use JsonSchema\Validator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class MigrationConfigDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $migrations = [];

    foreach (APIRef::loadMultiple() as $ref) {
      /** @var APIRef $ref */
      $path = static::getSourcePath($ref);
      if ($path === NULL) {
        continue;
      }

      $migrations += static::createSwaggerMigrations($ref->migrationID(), $ref->id(), $path, $base_plugin_definition);
      $migrations += \Drupal::moduleHandler()->invokeAll('api_reference_migrations', [$ref, $path, $base_plugin_definition]);
    }

    \Drupal::moduleHandler()->alter('api_reference_migrations', $migrations);

    return $migrations;
  }

  /**
   * Returns the referneced source file for an APIDocsEntity.
   *
   * @param APIRef $ref
   *
   * @return null|string
   */
  protected static function getSourcePath(APIRef $ref) {
    /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $source */
    $source = $ref->get('source');
    /** @var \Drupal\file\Entity\File[] $referenced */
    $referenced = $source->referencedEntities();
    if (count($referenced) === 0) {
      return NULL;
    }
    $referenced_file = reset($referenced);
    return $referenced_file->getFileUri();
  }

  /**
   * Extracs the version from an API Reference.
   *
   * @param APIRef $ref
   *
   * @return string|null
   *
   * @throws \Exception
   * @throws \Drupal\devportal_api_reference\Plugin\Swagger20ValidationException
   */
  public static function getVersionFromAPIRef(APIRef $ref) {
    $type = $ref->getType();
    $path = static::getSourcePath($ref);
    return static::getVersion($type, $path);
  }

  /**
   * Extracts the version from a source file.
   *
   * @param string $type
   *   Source file type.
   * @param string $path
   *   Source file path.
   *
   * @return string|null
   *
   * @throws \Exception
   * @throws \Drupal\devportal_api_reference\Plugin\Swagger20ValidationException
   */
  public static function getVersion($type, $path) {
    if (!$path) {
      return NULL;
    }

    switch ($type) {
      case 'swagger_2_0':
        return static::parseSwagger($path)['info']['version'];
    }

    return NULL;
  }

  /**
   * Parses and validates a Swagger file.
   *
   * @param string $file_path
   *   The path of the Swagger file.
   *
   * @return array
   *   An associative array of the parsed Swagger file.
   *
   * @throws \Exception
   * @throws \Drupal\devportal_api_reference\Plugin\Swagger20ValidationException
   */
  public static function parseSwagger($file_path) {
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
        // Parse the Swagger definition but DO NOT convert it to an array yet!
        $swagger = Yaml::parse(file_get_contents($file_path), Yaml::PARSE_OBJECT_FOR_MAP);
      }
      catch (ParseException $e) {
        throw new \Exception("Can not parse YAML source file ({$file_path}).");
      }
    }
    else if ($file_ext === 'json') {
      // Parse the Swagger definition but DO NOT convert it to an array yet!
      $swagger = json_decode(file_get_contents($file_path));
      if ($swagger === NULL) {
        throw new \Exception("The JSON source file ({$file_path}) cannot be decoded or the encoded data is deeper then the recursion limit (512).");
      }
    }
    else {
      throw new \Exception("Unsupported source file extension: $file_ext. Please use YAML or JSON source.");
    }

    static::validateSwagger($swagger);

    // Now that the validation is done we can convert the Swagger object into a
    // manageable associative array.
    $swagger = json_decode(json_encode($swagger), TRUE);

    $bin->set($cid, $swagger, Cache::PERMANENT);

    return $swagger;
  }

  /**
   * Validates a Swagger 2.0 document.
   *
   * @param object $swagger
   *   The Swagger object to validate.
   *
   * @throws Swagger20ValidationException
   */
  public static function validateSwagger($swagger) {
    $validator = new Validator();
    $validator->validate($swagger, (object)[
      '$ref' => 'file://' . ($_SERVER['DOCUMENT_ROOT'] ?: getcwd()) . '/' . drupal_get_path('module', 'devportal_api_reference') . '/data/swagger20-schema.json',
    ]);
    if (!$validator->isValid()) {
      $errors = $validator->getErrors();
      throw Swagger20ValidationException::fromErrors($errors);
    }
  }

  /**
   * Gets the table name suffix for a content type.
   *
   * @param string $type
   *
   * @return string|null
   */
  public static function tableSuffixForBundle($type) {
    static $map = [
      'swagger_2_0' => 'swagger_20',
    ];
    return isset($map[$type]) ? $map[$type] : NULL;
  }

  /**
   * Generates swagger migrations for a given swagger file.
   *
   * @param string $name
   *   Machine name prefix of the migrations.
   * @param int $api_ref_id
   *   The unique ID of the API Reference entity.
   * @param string $source_file_path
   *   Path of the source file.
   * @param array $base_plugin_definition
   *
   * @return array
   */
  public static function createSwaggerMigrations($name, $api_ref_id, $source_file_path, $base_plugin_definition) {
    return [];
  }

}
