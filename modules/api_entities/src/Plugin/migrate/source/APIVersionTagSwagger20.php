<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Version Tag entity.
 *
 * @MigrateSource(
 *   id = "dp_api_version_tag_swagger_20"
 * )
 */
class APIVersionTagSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
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
      'name' => [
        'type' => 'string',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function setSourceNodeKey(array &$node, $field_name, $path, $parent_source_key) {
    // There is no need to set source key for version tag entities.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    if (($path === '/') && !empty($node['info']['version']) && !isset($data['api_version_tag'])) {
      $data['api_version_tag'] = [
        'version' => $node['info']['version'],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_version_tag'])) {
      return new \ArrayIterator([]);
    }

    return new \ArrayIterator([[
      'name' => $data['api_version_tag']['version'],
      'api_ref_id' => $this->apiRefID,
    ]]);
  }

}
