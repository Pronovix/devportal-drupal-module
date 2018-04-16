<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Response Set entity.
 *
 * @MigrateSource(
 *   id = "dp_api_response_set_swagger_20"
 * )
 */
class APIResponseSetSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'api_method' => t('API Method'),
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
    if (($field_name === 'responses') && is_array($node)) {
      $node['_dp_source_key'] = $parent_source_key . '/responses';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_response_sets'])) {
      $data['api_response_sets'] = [];
    }

    if (($field_name === 'responses') && is_array($node)) {
      $data['api_response_sets'][$node['_dp_source_key']] = [
        'node' => $node,
        'api_method' => [
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

    if (empty($data['api_doc']) || empty($data['api_response_sets'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $response_sets = [];
    foreach ($data['api_response_sets'] as $source_key => $item) {
      $response_set = $item['node'];

      $api_method = [[$this->apiRefID, $item['api_method']['source_key']]];

      $extensions = [];
      foreach ($response_set as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $response_sets[] = [
        'api_method' => $api_method,
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($response_sets);
  }

}
