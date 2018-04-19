<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Response Header entity.
 *
 * @MigrateSource(
 *   id = "dp_api_response_header_swagger_20"
 * )
 */
class APIResponseHeaderSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'type' => t('Type'),
      'name' => t('Name'),
      'description' => t('Description'),
      'format' => t('Format'),
      'collection_format' => t('Collection format'),
      'items' => t('Items'),
      'default' => t('Default'),
      'response' => t('Response'),
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
    if (($field_name === 'headers') && is_array($node)) {
      foreach ($node as $field_name => &$value) {
        $value['_dp_source_key'] = $parent_source_key . '/header/' . $field_name;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_response_headers'])) {
      $data['api_response_headers'] = [];
    }

    if (($field_name === 'headers') && is_array($node)) {
      foreach ($node as $field_name => &$value) {
        $data['api_response_headers'][$value['_dp_source_key']] = [
          'name' => $field_name,
          'node' => $value,
          'api_response' => [
            'source_key' => $parent_source_key,
          ],
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_response_headers'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $response_headers = [];
    foreach ($data['api_response_headers'] as $source_key => $item) {
      $response_header = $item['node'];
      $name = $item['name'];
      $api_response = $item['api_response'];

      $response = [[$this->apiRefID, $api_response['source_key']]];

      $items = [];
      if (!empty($response_header['items'])) {
        $items[] = [$this->apiRefID, $source_key . '/items'];
      }

      $extensions = [];
      foreach ($response_header as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $response_headers[] = [
        'type' => $response_header['type'],
        'name' => $name,
        'description' => empty($response_header['description']) ? '' : $response_header['description'],
        'format' => empty($response_header['format']) ? '' : $response_header['format'],
        'collection_format' => empty($response_header['collectionFormat']) ? '' : $response_header['collectionFormat'],
        'items' => $items,
        'default' => empty($response_header['default']) ? '' : $response_header['default'],
        'response' => $response,
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($response_headers);
  }

}
