<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Body Param entity.
 *
 * @MigrateSource(
 *   id = "dp_api_body_param_swagger_20"
 * )
 */
class APIBodyParamSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'description' => t('Description'),
      'required' => t('Required'),
      'schema' => t('Schema'),
      'api_version' => t('API Version'),
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
        if (!empty($parameter['in']) && ($parameter['in'] === 'body')) {
          if ($path === '/parameters/') {
            // Set source key on a global parameter.
            $parameter['_dp_source_key'] = '#/parameters/' . $key;
          }
          else {
            // Set source key on a non-global parameter.
            $parameter['_dp_source_key'] = $parent_source_key . '/parameters/body/' . $parameter['name'];
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
    if (!isset($data['api_body_params'])) {
      $data['api_body_params'] = [];
    }

    if (($field_name === 'parameters') && is_array($node)) {
      foreach ($node as $key => &$parameter) {
        if (!empty($parameter['in']) && ($parameter['in'] === 'body')) {
          // This can be either a global or a non-global parameter.
          $data['api_body_params'][$parameter['_dp_source_key']] = [
            'node' => $parameter,
          ];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_body_params'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $body_params = [];
    foreach ($data['api_body_params'] as $source_key => $item) {
      $body_param = $item['node'];

      $schema = [];
      if (!empty($body_param['schema'])) {
        if (!empty($body_param['schema']['$ref'])) {
          $schema[] = [$this->apiRefID, $body_param['schema']['$ref']];
        }
        else {
          $schema[] = [$this->apiRefID, $body_param['schema']['_dp_source_key']];
        }
      }

      $body_params[] = [
        'name' => $body_param['name'],
        'description' => empty($body_param['description']) ? '' : $body_param['description'],
        'required' => empty($body_param['required']) ? '' : $body_param['required'],
        'schema' => $schema,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($body_params);
  }

}
