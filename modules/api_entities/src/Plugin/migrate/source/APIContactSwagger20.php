<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Contact entity.
 *
 * @MigrateSource(
 *   id = "dp_api_contact_swagger_20"
 * )
 */
class APIContactSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'url' => t('URL'),
      'email' => t('Email'),
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
    // There is no need to set source key on the Contact object.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    if (($path === '/') && !empty($node['info']['contact']) && !isset($data['api_contact'])) {
      $data['api_contact'] = [
        'node' => $node['info']['contact'],
      ];
    }
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_contact'])) {
      return new \ArrayIterator([]);
    }

    $api_contact = $data['api_contact']['node'];
    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $extensions = [];
    foreach ($api_contact as $field_name => $value) {
      if (substr($field_name, 0, 2) === 'x-') {
        $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
      }
    }

    return new \ArrayIterator([[
      'name' => empty($api_contact['name']) ? '' : $api_contact['name'],
      'url' => empty($api_contact['url']) ? '' : $api_contact['url'],
      'email' => empty($api_contact['email']) ? '' : $api_contact['email'],
      'extensions' => $extensions,
      'api_version' => $api_version,
      'api_ref_id' => $this->apiRefID,
    ]]);
  }

}
