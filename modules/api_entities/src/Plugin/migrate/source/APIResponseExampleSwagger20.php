<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Response Example entity.
 *
 * @MigrateSource(
 *   id = "dp_api_response_example_swagger_20"
 * )
 */
class APIResponseExampleSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'example' => t('Example'),
      'response' => t('Response'),
      'produces' => t('Produces'),
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
    if (($field_name === 'examples') && is_array($node)) {
      $node['_dp_source_key'] = $parent_source_key . '/example';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_response_examples'])) {
      $data['api_response_examples'] = [];
    }

    if (($field_name === 'examples') && is_array($node)) {
      $data['api_response_examples'][$node['_dp_source_key']] = [
        'node' => $node,
        'api_response' => [
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

    if (empty($data['api_doc']) || empty($data['api_response_examples'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $response_examples = [];
    foreach ($data['api_response_examples'] as $source_key => $item) {
      $response_example = $item['node'];
      $api_response = $item['api_response'];

      $response = [[$this->apiRefID, $api_response['source_key']]];

      $produces = [];
      foreach (array_keys($response_example) as $mime_type) {
        $produces[] = [$this->apiRefID, $mime_type];
      }

      // Remove dp_source_key from response example before serialization.
      unset($response_example['_dp_source_key']);

      $response_examples[] = [
        'example' => json_encode($response_example),
        'response' => $response,
        'produces' => $produces,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($response_examples);
  }

}
