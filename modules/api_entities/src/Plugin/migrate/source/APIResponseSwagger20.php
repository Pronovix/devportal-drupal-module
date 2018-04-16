<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Response entity.
 *
 * @MigrateSource(
 *   id = "dp_api_response_swagger_20"
 * )
 */
class APIResponseSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'code' => t('Example'),
      'description' => t('Description'),
      'api_schema' => t('API Schema'),
      'api_response_set' => t('API Response Set'),
      'api_global_response' => t('API Global Response'),
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
    $path_parts = explode('/', $parent_source_key);
    if ((end($path_parts) === 'responses') && is_array($node)) {
      $node['_dp_source_key'] = $parent_source_key . '/' . $field_name;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_responses'])) {
      $data['api_responses'] = [];
    }

    $path_parts = explode('/', $parent_source_key);
    if ((end($path_parts) === 'responses') && is_array($node)) {
      $data['api_responses'][$node['_dp_source_key']] = [
        'code' => ($field_name === 'default') ? 0 : (int) $field_name,
        'node' => $node,
        'api_response_set' => [
          'source_key' => $parent_source_key,
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_responses'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $responses = [];
    foreach ($data['api_responses'] as $source_key => $item) {
      $response = $item['node'];
      $code = $item['code'];

      $api_schema = [];
      if (!empty($response['schema'])) {
        if (!empty($response['schema']['$ref'])) {
          $api_schema[] = [$this->apiRefID, $response['schema']['$ref']];
        }
        else {
          $api_schema[] = [$this->apiRefID, $response['schema']['_dp_source_key']];
        }
      }

      $api_response_set = [[$this->apiRefID, $item['api_response_set']['source_key']]];

      $api_global_response = [];
      if (!empty($response['$ref'])) {
        $api_global_response[] = [$this->apiRefID, $response['$ref']];
      }

      $extensions = [];
      foreach ($response as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $responses[] = [
        'code' => $code,
        'description' => empty($response['description']) ? '' : $response['description'],
        'api_schema' => $api_schema,
        'api_response_set' => $api_response_set,
        'api_global_response' => $api_global_response,
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($responses);
  }

}
