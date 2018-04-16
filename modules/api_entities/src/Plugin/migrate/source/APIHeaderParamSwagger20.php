<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Header Param entity.
 *
 * @MigrateSource(
 *   id = "dp_api_header_param_swagger_20"
 * )
 */
class APIHeaderParamSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'description' => t('Description'),
      'required' => t('Required'),
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
        if (!empty($parameter['in']) && ($parameter['in'] === 'header')) {
          if ($path === '/parameters/') {
            // Set source key on a global parameter.
            $parameter['_dp_source_key'] = '#/parameters/' . $key;
          }
          else {
            // Set source key on a non-global parameter.
            $parameter['_dp_source_key'] = $parent_source_key . '/parameters/header/' . $parameter['name'];
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
    if (!isset($data['api_header_params'])) {
      $data['api_header_params'] = [];
    }

    if (($field_name === 'parameters') && is_array($node)) {
      foreach ($node as $key => &$parameter) {
        if (!empty($parameter['in']) && ($parameter['in'] === 'header')) {
          // This can be either a global or a non-global parameter.
          $data['api_header_params'][$parameter['_dp_source_key']] = [
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

    if (empty($data['api_doc']) || empty($data['api_header_params'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $header_params = [];
    foreach ($data['api_header_params'] as $source_key => $item) {
      $header_param = $item['node'];

      $items = [];
      if (!empty($header_param['items'])) {
        $items[] = [$this->apiRefID, $source_key . '/items'];
      }

      $header_params[] = [
        'name' => $header_param['name'],
        'description' => empty($header_param['description']) ? '' : $header_param['description'],
        'required' => empty($header_param['required']) ? '' : $header_param['required'],
        'type' => $header_param['type'],
        'format' => empty($header_param['format']) ? '' : $header_param['format'],
        'collection_format' => empty($header_param['collectionFormat']) ? '' : $header_param['collectionFormat'],
        'default' => empty($header_param['default']) ? '' : $header_param['default'],
        'items' => $items,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($header_params);
  }

}
