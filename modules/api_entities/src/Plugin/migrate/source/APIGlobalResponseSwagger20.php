<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Global Response entity.
 *
 * @MigrateSource(
 *   id = "dp_api_global_response_swagger_20"
 * )
 */
class APIGlobalResponseSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'description' => t('Description'),
      'extensions' => t('Extensions'),
      'api_schema' => t('API schema'),
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
    // There is no need to set source key for global response entities.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (($path === '/') && !isset($data['api_global_responses'])) {
      $data['api_global_responses'] = [];
    }

    if (($path === '/') && !empty($node['responses']) && is_array($node['responses'])) {
      foreach ($node['responses'] as $name => $definition) {
        $data['api_global_responses']['#/responses/' . $name] = [
          'name' => $name,
          'node' => $definition,
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_global_responses'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $global_responses = [];
    foreach ($data['api_global_responses'] as $source_key => $item) {
      $global_response = $item['node'];
      $name = $item['name'];

      $api_schema = [];
      if (!empty($global_response['schema'])) {
        if (!empty($global_response['schema']['$ref'])) {
          $api_schema[] = [$this->apiRefID, $global_response['schema']['$ref']];
        }
        else {
          $api_schema[] = [$this->apiRefID, $global_response['schema']['_dp_source_key']];
        }
      }

      $extensions = [];
      foreach ($global_response as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $global_responses[] = [
        'name' => $name,
        'description' => $global_response['description'],
        'extensions' => $extensions,
        'api_schema' => $api_schema,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($global_responses);
  }

}
