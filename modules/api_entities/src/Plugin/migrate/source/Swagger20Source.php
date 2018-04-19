<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

use Drupal\devportal_api_reference\Plugin\MigrationConfigDeriver;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateException;

/**
 * Abstract class for Swagger 2.0 source plugins.
 */
abstract class Swagger20Source extends SourcePluginBase {

  /**
   * Slice from the APIRef entity's UUID.
   *
   * @var integer
   */
  protected $apiRefID;

  /**
   * Source file path.
   *
   * @var string
   */
  protected $sourceFile;

  /**
   * Source classes.
   *
   * @var array
   */
  protected $sourceClasses = [];

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    $this->apiRefID = $configuration['api_ref_id'];
    $this->sourceFile = $configuration['source_file'];

    /** @var \Drupal\Component\Plugin\PluginManagerInterface $source_plugin_manager */
    $source_plugin_manager = \Drupal::service('plugin.manager.migrate.source');
    // Collect migrate source classes which implement Swagger20SourceInterface.
    foreach ($source_plugin_manager->getDefinitions() as $definition) {
      if (in_array('Drupal\devportal_api_entities\Plugin\migrate\source\Swagger20SourceInterface', class_implements($definition['class']))) {
        $this->sourceClasses[] = $definition['class'];
      }
    }
  }

  /**
   * Retrieves collected source data from the Swagger object.
   *
   * @return mixed
   *  The collected source data.
   *
   * @throws \Drupal\migrate\MigrateException
   *  If the source file can not be parsed or if its type is unsupported.
   */
  public function getSourceData() {
    $data = [];
    $data_cid = "devportal_api_entities:source_file:{$this->sourceFile}";

    // Try to retrieve the source data from cache.
    if ($cache = \Drupal::cache()->get($data_cid)) {
      return $cache->data;
    }

    try {
      $swagger = MigrationConfigDeriver::parseSwagger($this->sourceFile);
    }
    catch (\Exception $e) {
      throw new MigrateException($e->getMessage(), 0, $e);
    }

    if (!empty($swagger)) {
      // Set unique source keys in the Swagger object recursively.
      $this->setSourceNodeKeyRec($swagger, '', '/', '');
      // Collect data from the Swagger object recursively.
      $this->collectSourceNodeDataRec($swagger, '', '/', '', $data);
    }

    // Cache the calculated source data.
    \Drupal::cache()->set($data_cid, $data);

    return $data;
  }

  /**
   * Sets source node key in the Swagger object recursively.
   *
   * @param $node
   *   The actual node in the Swagger object being processed.
   * @param $field_name
   *   The field name of the node in its parent.
   * @param $path
   *   The path to the node in the Swagger object.
   * @param $parent_source_key
   *   The unique source key of the parent node.
   */
  protected function setSourceNodeKeyRec(array &$node, $field_name, $path, $parent_source_key) {
    /** @var \Drupal\devportal_api_entities\Plugin\migrate\source\Swagger20SourceInterface $class */
    foreach ($this->sourceClasses as $class) {
      $class::setSourceNodeKey($node, $field_name, $path, $parent_source_key);
    }

    // Call the method recursively if case of object or array properties.
    foreach (array_keys($node) as $field_name) {
      // Do not go into the /definitions subtree.
      if (($path === '/') && (($field_name === 'definitions') || ($field_name === 'responses'))) {
        continue;
      }
      // Do not go into schema extensions related subtrees.
      elseif (substr($field_name, 0, 2) === 'x-') {
        continue;
      }
      elseif (is_array($node[$field_name])) {
        $dp_source_key = isset($node['_dp_source_key']) ? $node['_dp_source_key'] : $parent_source_key;
        $this->setSourceNodeKeyRec($node[$field_name], $field_name,$path . $field_name . '/', $dp_source_key);
      }
    }
  }

  /**
   * Collects source node data in the Swagger object recursively.
   *
   * @param $node
   *   The actual node in the Swagger object being processed.
   * @param $field_name
   *   The field name of the node in its parent.
   * @param $path
   *   The path to the node in the Swagger object.
   * @param $parent_source_key
   *   The unique source key of the parent node.
   * @param array $data
   *   An array of collected source data.
   */
  protected function collectSourceNodeDataRec(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    /** @var \Drupal\devportal_api_entities\Plugin\migrate\source\Swagger20SourceInterface $class */
    foreach ($this->sourceClasses as $class) {
      $class::collectSourceNodeData($node, $field_name, $path, $parent_source_key, $data);
    }

    // Call the method recursively if case of object or array properties.
    foreach (array_keys($node) as $field_name) {
      // Do not go into the /definitions subtree.
      if (($path === '/') && (($field_name === 'definitions') || ($field_name === 'responses'))) {
        continue;
      }
      // Do not go into schema extensions related subtrees.
      elseif (substr($field_name, 0, 2) === 'x-') {
        continue;
      }
      elseif (is_array($node[$field_name])) {
        $dp_source_key = isset($node['_dp_source_key']) ? $node['_dp_source_key'] : $parent_source_key;
        $this->collectSourceNodeDataRec($node[$field_name], $field_name,$path . $field_name . '/', $dp_source_key, $data);
      }
    }
  }

  /**
   * Prints the source URL when the object is used as a string.
   *
   * @return string
   *   The source URL.
   */
  public function __toString() {
    return $this->sourceFile;
  }

}
