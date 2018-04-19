<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Schema entity.
 *
 * @MigrateSource(
 *   id = "dp_api_schema_swagger_20"
 * )
 */
class APISchemaSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'inline_schema' => t('Inline schema'),
      'global_schema' => t('Global schema'),
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
    if (($field_name === 'schema') && is_array($node)) {
      // Do not set source key on a global schema reference.
      if (empty($node['$ref'])) {
        $node['_dp_source_key'] = $parent_source_key . '/schema';
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_schemas'])) {
      $data['api_schemas'] = [];
    }

    if (($field_name === 'schema') && is_array($node)) {
      // Global schema reference.
      if (!empty($node['$ref'])) {
        $data['api_schemas'][$node['$ref']] = [
          'node' => $node,
        ];
      }
      // Non-global schema reference.
      else {
        $data['api_schemas'][$node['_dp_source_key']] = [
          'node' => $node,
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

    if (empty($data['api_doc']) || empty($data['api_schemas'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $schemas = [];
    foreach ($data['api_schemas'] as $source_key => $item) {
      $schema = $item['node'];

      $global_schema = [];
      if (!empty($schema['$ref'])) {
        $global_schema[] = [$this->apiRefID, $schema['$ref']];
      }

      $ext_doc = [];
      if (!empty($schema['externalDocs'])) {
        $ext_doc[] = [$this->apiRefID, $schema['externalDocs']['_dp_source_key']];
      }

      // Remove dp_source_key from schema before serialization.
      unset($schema['_dp_source_key']);

      $schemas[] = [
        'inline_schema' => empty($schema['$ref']) ? json_encode($schema) : '',
        'global_schema' => $global_schema,
        'ext_doc' => $ext_doc,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($schemas);
  }

}
