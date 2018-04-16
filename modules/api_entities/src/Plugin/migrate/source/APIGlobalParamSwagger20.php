<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Global Param entity.
 *
 * @MigrateSource(
 *   id = "dp_api_global_param_swagger_20"
 * )
 */
class APIGlobalParamSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'meta_param' => t('Meta parameter'),
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
    // There is no need to set source key for global parameter entities.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_global_params'])) {
      $data['api_global_params'] = [];
    }

    if (($path === '/parameters/') && is_array($node)) {
      foreach ($node as $parameter) {
        if (!empty($parameter['in']) && is_string($parameter['in']) && in_array($parameter['in'], ['path', 'body', 'query', 'header', 'formData'], TRUE)) {
          $data['api_global_params'][$parameter['_dp_source_key']] = [
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

    if (empty($data['api_doc']) || empty($data['api_global_params'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $global_params = [];
    foreach ($data['api_global_params'] as $source_key => $item) {
      $global_param = $item['node'];

      $meta_param = [[$this->apiRefID, $source_key]];

      $global_params[] = [
        'name' => $global_param['name'],
        'meta_param' => $meta_param,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($global_params);
  }

}
