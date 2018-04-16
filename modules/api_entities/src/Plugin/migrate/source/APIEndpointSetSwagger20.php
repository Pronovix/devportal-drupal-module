<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Endpoint Set entity.
 *
 * @MigrateSource(
 *   id = "dp_api_endpoint_set_swagger_20"
 * )
 */
class APIEndpointSetSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'extensions' => t('Extensions'),
      'api_version' => t('API version'),
      'api_ref_id' => t('API Reference ID'),
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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function setSourceNodeKey(array &$node, $field_name, $path, $parent_source_key) {
    // There is no need to set source key on the API Endpoint Set object.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    if (($path === '/paths/') && !isset($data['api_endpoint_set'])) {
      $data['api_endpoint_set'] = [
        'node' => $node,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_endpoint_set'])) {
      return new \ArrayIterator([]);
    }

    $endpoint_set = $data['api_endpoint_set']['node'];
    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $extensions = [];
    foreach ($endpoint_set as $field_name => $value) {
      if (substr($field_name, 0, 2) === 'x-') {
        $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
      }
    }

    return new \ArrayIterator([[
      'extensions' => $extensions,
      'api_version' => $api_version,
      'api_ref_id' => $this->apiRefID,
    ]]);
  }

}
