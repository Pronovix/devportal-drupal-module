<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Query Param entity.
 *
 * @MigrateSource(
 *   id = "dp_api_query_param_swagger_20"
 * )
 */
class APIQueryParamSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'description' => t('Description'),
      'required' => t('Required'),
      'allow_empty_value' => t('Allow empty value'),
      'type' => t('Type'),
      'format' => t('Format'),
      'collection_format' => t('Collection format'),
      'default' => t('Default'),
      'items' => t('Items'),
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
    if (($field_name === 'parameters') && is_array($node)) {
      foreach ($node as $key => &$parameter) {
        if (!empty($parameter['in']) && ($parameter['in'] === 'query')) {
          if ($path === '/parameters/') {
            // Set source key on a global parameter.
            $parameter['_dp_source_key'] = '#/parameters/' . $key;
          }
          else {
            // Set source key on a non-global parameter.
            $parameter['_dp_source_key'] = $parent_source_key . '/parameters/query/' . $parameter['name'];
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_query_params'])) {
      $data['api_query_params'] = [];
    }

    if (($field_name === 'parameters') && is_array($node)) {
      foreach ($node as $key => &$parameter) {
        if (!empty($parameter['in']) && ($parameter['in'] === 'query')) {
          // This can be either a global or a non-global parameter.
          $data['api_query_params'][$parameter['_dp_source_key']] = [
            'node' => $parameter,
          ];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_query_params'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $query_params = [];
    foreach ($data['api_query_params'] as $source_key => $item) {
      $query_param = $item['node'];

      $items = [];
      if (!empty($query_param['items'])) {
        $items[] = [$this->apiRefID, $source_key . '/items'];
      }

      $query_params[] = [
        'name' => $query_param['name'],
        'description' => empty($query_param['description']) ? '' : $query_param['description'],
        'required' => empty($query_param['required']) ? '' : $query_param['required'],
        'allow_empty_value' => empty($query_param['allowEmptyValue']) ? '' : $query_param['allowEmptyValue'],
        'type' => $query_param['type'],
        'format' => empty($query_param['format']) ? '' : $query_param['format'],
        'collection_format' => empty($query_param['collectionFormat']) ? '' : $query_param['collectionFormat'],
        'default' => empty($query_param['default']) ? '' : $query_param['default'],
        'items' => $items,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($query_params);
  }

}
