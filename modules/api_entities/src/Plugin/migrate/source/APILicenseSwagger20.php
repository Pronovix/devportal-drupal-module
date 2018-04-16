<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API License entity.
 *
 * @MigrateSource(
 *   id = "dp_api_license_swagger_20"
 * )
 */
class APILicenseSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'url' => t('URL'),
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
    // There is no need to set source key on the License object.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    if (($path === '/') && !empty($node['info']['license']) && !isset($data['api_license'])) {
      $data['api_license'] = [
        'node' => $node['info']['license'],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_license'])) {
      return new \ArrayIterator([]);
    }

    $api_license = $data['api_license']['node'];
    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $extensions = [];
    foreach ($api_license as $field_name => $value) {
      if (substr($field_name, 0, 2) === 'x-') {
        $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
      }
    }

    return new \ArrayIterator([[
      'name' => $api_license['name'],
      'url' => empty($api_license['url']) ? '' : $api_license['url'],
      'extensions' => $extensions,
      'api_version' => $api_version,
      'api_ref_id' => $this->apiRefID,
    ]]);
  }

}
