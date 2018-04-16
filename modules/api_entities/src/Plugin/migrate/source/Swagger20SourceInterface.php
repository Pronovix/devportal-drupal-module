<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

interface Swagger20SourceInterface {

  /**
   * Sets source node key in the Swagger object.
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
  public static function setSourceNodeKey(array &$node, $field_name, $path, $parent_source_key);

  /**
   * Collects source node data in the Swagger object.
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
   public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data);

}
