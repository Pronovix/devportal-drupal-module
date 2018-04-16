<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API MIME Type vocabulary terms.
 *
 * @MigrateSource(
 *   id = "dp_api_mime_type_swagger_20"
 * )
 */
class APIMimeTypeSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'mime_type' => t('MIME Type'),
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
      'mime_type' => [
        'type' => 'string',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function setSourceNodeKey(array &$node, $field_name, $path, $parent_source_key) {
    // There is no need to set source key for mime types.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_mime_types'])) {
      $data['api_mime_types'] = [];
    }

    foreach (array_keys($node) as $field_name) {
      if (in_array($field_name, ['consumes', 'produces'], TRUE) && is_array($node[$field_name])) {
        $data['api_mime_types'] = array_unique(array_merge($data['api_mime_types'], $node[$field_name]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_mime_types'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $mime_types = [];
    foreach ($data['api_mime_types'] as $mime_type) {
      $mime_types[] = [
        'mime_type' => $mime_type,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
      ];
    }

    return new \ArrayIterator($mime_types);
  }

}
