<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Endpoint entity.
 *
 * @MigrateSource(
 *   id = "dp_api_endpoint_swagger_20"
 * )
 */
class APIEndpointSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'uri' => t('URI'),
      'params' => t('Parameters'),
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
    if ($path === '/paths/') {
      foreach (array_keys($node) as $field_name) {
        // Skip schema extensions.
        if (substr($field_name, 0, 2) === 'x-') {
          continue;
        }

        $node[$field_name]['_dp_source_key'] = $field_name;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_endpoints'])) {
      $data['api_endpoints'] = [];
    }

    if ($path === '/paths/') {
      foreach (array_keys($node) as $field_name) {
        // Skip schema extensions.
        if (substr($field_name, 0, 2) === 'x-') {
          continue;
        }

        $data['api_endpoints'][$node[$field_name]['_dp_source_key']] = [
          'node' => [
            'uri' => $field_name,
          ],
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_endpoints'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $endpoints = [];
    foreach ($data['api_endpoints'] as $source_key => $item) {
      $endpoint = $item['node'];

      $params = [];
      if (!empty($endpoint['parameters'])) {
        foreach ($endpoint['parameters'] as $parameter) {
          if (!empty($parameter['$ref'])) {
            $params[] = [$this->apiRefID, $parameter['$ref']];
          }
          else if (!empty($parameter['in'])) {
            $params[] = [$this->apiRefID, $parameter['_dp_source_key']];
          }
        }
      }

      $extensions = [];
      foreach ($endpoint as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $endpoints[] = [
        'uri' => $endpoint['uri'],
        'params' => $params,
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($endpoints);
  }

}
