<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API External Documentation entity.
 *
 * @MigrateSource(
 *   id = "dp_api_ext_doc_swagger_20"
 * )
 */
class APIExtDocSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'url' => t('URL'),
      'description' => t('Description'),
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
    if (($field_name === 'externalDocs') && is_array($node)) {
      $node['_dp_source_key'] = $parent_source_key . '/' . $node['url'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_ext_docs'])) {
      $data['api_ext_docs'] = [];
    }

    if (($field_name === 'externalDocs') && is_array($node)) {
      $data['api_ext_docs'][$node['_dp_source_key']] = [
        'node' => $node,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_ext_docs'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $ext_docs = [];
    foreach ($data['api_ext_docs'] as $source_key => $item) {
      $ext_doc = $item['node'];

      $extensions = [];
      foreach ($ext_doc as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $ext_docs[] = [
        'url' => $ext_doc['url'],
        'description' => empty($ext_doc['description']) ? '' : $ext_doc['description'],
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($ext_docs);
  }

}
