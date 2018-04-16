<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Param Item entity.
 *
 * @MigrateSource(
 *   id = "dp_api_param_item_swagger_20"
 * )
 */
class APIParamItemSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'type' => t('Type'),
      'format' => t('Format'),
      'collection_format' => t('Collection format'),
      'default' => t('Default'),
      'items' => t('Items'),
      'extensions' => t('Extensions'),
      'api_version' => t('API version'),
      'api_ref_id' => t('API Reference ID'),
      'source_key' => t('Source key'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'api_ref_id' => [
        'type' => 'string',
      ],
      'source_key' => [
        'type' => 'string',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function setSourceNodeKey(array &$node, $field_name, $path, $parent_source_key) {
    if (($field_name === 'items') && is_array($node)) {
      $path_parts = explode('/', $parent_source_key);
      // Do not process the "items" field of schema definitions.
      if (end($path_parts) !== 'schema') {
        $node['_dp_source_key'] = $parent_source_key . '/items';
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_param_items'])) {
      $data['api_param_items'] = [];
    }

    if (($field_name === 'items') && is_array($node)) {
      $path_parts = explode('/', $parent_source_key);
      // Do not process the "items" field of schema definitions.
      if (end($path_parts) !== 'schema') {
        $data['api_param_items'][$node['_dp_source_key']] = [
          'node' => $node,
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_param_items'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $param_items = [];
    foreach ($data['api_param_items'] as $source_key => $item) {
      $param_item = $item['node'];

      $items = [];
      if (!empty($param_item['items'])) {
        $items[] = [$this->apiRefID, $source_key . '/items'];
      }

      $extensions = [];
      foreach ($param_item as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $param_items[] = [
        'type' => $param_item['type'],
        'format' => empty($param_item['format']) ? '' : $param_item['format'],
        'collection_format' => empty($param_item['collectionFormat']) ? '' : $param_item['collectionFormat'],
        'default' => empty($param_item['default']) ? '' : $param_item['default'],
        'items' => $items,
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($param_items);
  }

}
