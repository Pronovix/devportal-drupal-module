<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Info entity.
 *
 * @MigrateSource(
 *   id = "dp_api_info_swagger_20"
 * )
 */
class APIInfoSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'title' => t('Title'),
      'description' => t('Description'),
      'terms_of_service' => t('Terms of service'),
      'contact' => t('Contact'),
      'license' => t('License'),
      'version' => t('Version'),
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
    // There is no need to set source key on the Info object.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    if (($path === '/') && !isset($data['api_info'])) {
      $data['api_info'] = [
        'node' => $node['info'],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_info'])) {
      return new \ArrayIterator([]);
    }

    $api_info = $data['api_info']['node'];
    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $contact = [];
    if (!empty($api_info['contact'])) {
      $contact[] = [$this->apiRefID];
    }

    $license = [];
    if (!empty($api_info['license'])) {
      $license[] = [$this->apiRefID];
    }

    $extensions = [];
    foreach ($api_info as $field_name => $value) {
      if (substr($field_name, 0, 2) === 'x-') {
        $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
      }
    }

    return new \ArrayIterator([[
      'title' => $api_info['title'],
      'description' => empty($api_info['description']) ? '' : $api_info['description'],
      'terms_of_service' => empty($api_info['terms_of_service']) ? '' : $api_info['terms_of_service'],
      'contact' => $contact,
      'license' => $license,
      'version' => $api_info['version'],
      'extensions' => $extensions,
      'api_version' => $api_version,
      'api_ref_id' => $this->apiRefID,
    ]]);
  }

}
