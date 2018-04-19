<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Meta Param entity.
 *
 * @MigrateSource(
 *   id = "dp_api_meta_param_swagger_20"
 * )
 */
class APIMetaParamSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'in' => t('In'),
      'path_param' => t('Path parameter'),
      'body_param' => t('Body parameter'),
      'query_param' => t('Query parameter'),
      'header_param' => t('Header parameter'),
      'form_data_param' => t('Form data parameter'),
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
    // There is no need to set source key for meta parameter entities.
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_meta_params'])) {
      $data['api_meta_params'] = [];
    }

    if (($field_name === 'parameters') && is_array($node)) {
      foreach ($node as $parameter) {
        if (!empty($parameter['in']) && is_string($parameter['in']) && in_array($parameter['in'], ['path', 'body', 'query', 'header', 'formData'], TRUE)) {
          // This can be either a global or a non-global parameter.
          $data['api_meta_params'][$parameter['_dp_source_key']] = [
            'node' => $parameter,
          ];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_meta_params'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $meta_params = [];
    foreach ($data['api_meta_params'] as $source_key => $item) {
      $meta_param = $item['node'];

      $path_param = [];
      if ($meta_param['in'] === 'path') {
        $path_param[] = [$this->apiRefID, $source_key];
      }

      $body_param = [];
      if ($meta_param['in'] === 'body') {
        $body_param[] = [$this->apiRefID, $source_key];
      }

      $query_param = [];
      if ($meta_param['in'] === 'query') {
        $query_param[] = [$this->apiRefID, $source_key];
      }

      $header_param = [];
      if ($meta_param['in'] === 'header') {
        $header_param[] = [$this->apiRefID, $source_key];
      }

      $form_data_param = [];
      if ($meta_param['in'] === 'formData') {
        $form_data_param[] = [$this->apiRefID, $source_key];
      }

      $extensions = [];
      foreach ($meta_param as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $meta_params[] = [
        'name' => $meta_param['name'],
        'in' => $meta_param['in'],
        'path_param' => $path_param,
        'body_param' => $body_param,
        'query_param' => $query_param,
        'header_param' => $header_param,
        'form_data_param' => $form_data_param,
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($meta_params);
  }

}
