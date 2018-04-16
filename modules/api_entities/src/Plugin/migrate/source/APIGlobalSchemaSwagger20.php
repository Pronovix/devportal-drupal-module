<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Global Schema entity.
 *
 * @MigrateSource(
 *   id = "dp_api_global_schema_swagger_20"
 * )
 */
class APIGlobalSchemaSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'value' => t('Value'),
      'ext_doc' => t('External documentation'),
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
    // There is no need to set source key for global schema entities.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (($path === '/') && !isset($data['api_global_schemas'])) {
      $data['api_global_schemas'] = [];
    }

    if (($path === '/') && !empty($node['definitions']) && is_array($node['definitions'])) {
      foreach ($node['definitions'] as $name => $definition) {
        $data['api_global_schemas']['#/definitions/' . $name] = [
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

    if (empty($data['api_doc']) || empty($data['api_global_schemas'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $global_schemas = [];
    foreach ($data['api_global_schemas'] as $source_key => $item) {
      $global_schema = $item['node'];
      $name = $item['name'];

      $ext_doc = [];
      if (!empty($global_schema['externalDocs'])) {
        $ext_doc[] = [$this->apiRefID, $global_schema['externalDocs']['_dp_source_key']];
      }

      // Remove dp_source_key from global schema before serialization.
      unset($global_schema['_dp_source_key']);

      $global_schemas[] = [
        'name' => $name,
        'value' => json_encode($global_schema),
        'ext_doc' => $ext_doc,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($global_schemas);
  }

}
