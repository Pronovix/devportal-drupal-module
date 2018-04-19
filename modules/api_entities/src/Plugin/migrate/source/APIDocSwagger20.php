<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Documentation entity.
 *
 * @MigrateSource(
 *   id = "dp_api_doc_swagger_20"
 * )
 */
class APIDocSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'host' => t('Host'),
      'base_path' => t('Base path'),
      'protocol' => t('Protocol'),
      'consumes' => t('Consumes'),
      'produces' => t('Produces'),
      'api_ref_id' => t('API Reference ID'),
      'source_file' => t('Source file path'),
      'destination_file' => t('Destination file path'),
      'ext_doc' => t('External documentation'),
      'extensions' => t('Extensions'),
      'api_version' => t('API version'),
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
    // There is no need to set source key on the Swagger object.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    if (($path === '/') && !isset($data['api_doc'])) {
      $data['api_doc'] = [
        'node' => $node,
      ];
    }
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc'])) {
      return new \ArrayIterator([]);
    }

    $api_doc = $data['api_doc']['node'];
    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $consumes = [];
    if (!empty($api_doc['consumes'])) {
      foreach ($api_doc['consumes'] as $mime_type) {
        $consumes[] = [$this->apiRefID, $mime_type];
      }
    }

    $produces = [];
    if (!empty($api_doc['produces'])) {
      foreach ($api_doc['produces'] as $mime_type) {
        $produces[] = [$this->apiRefID, $mime_type];
      }
    }

    $ext_doc = [];
    if (!empty($api_doc['externalDocs'])) {
      $ext_doc[] = [$this->apiRefID, $api_doc['externalDocs']['_dp_source_key']];
    }

    $extensions = [];
    foreach ($api_doc as $field_name => $value) {
      if (substr($field_name, 0, 2) === 'x-') {
        $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
      }
    }

    return new \ArrayIterator([[
      'host' => empty($api_doc['host']) ? '' : $api_doc['host'],
      'base_path' => empty($api_doc['basePath']) ? '' : $api_doc['basePath'],
      'protocol' => empty($api_doc['schemes']) ? [] : $api_doc['schemes'],
      'consumes' => $consumes,
      'produces' => $produces,
      'api_ref_id' => $this->apiRefID,
      'source_file' => $this->sourceFile,
      'destination_file' => 'private://swagger_20_source/' . basename($this->sourceFile),
      'ext_doc' => $ext_doc,
      'extensions' => $extensions,
      'api_version' => $api_version,
    ]]);
  }

}
