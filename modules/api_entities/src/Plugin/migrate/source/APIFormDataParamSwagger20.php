<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Form Data Param entity.
 *
 * @MigrateSource(
 *   id = "dp_api_form_data_param_swagger_20"
 * )
 */
class APIFormDataParamSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'description' => t('Description'),
      'required' => t('Required'),
      'allow_empty_value' => t('Allow empty value'),
      'type' => t('Type'),
      'format' => t('Format'),
      'collection_format' => t('Collection format'),
      'default' => t('Default'),
      'items' => t('Items'),
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
    if (($field_name === 'parameters') && is_array($node)) {
      foreach ($node as $key => &$parameter) {
        if (!empty($parameter['in']) && ($parameter['in'] === 'formData')) {
          if ($path === '/parameters/') {
            // Set source key on a global parameter.
            $parameter['_dp_source_key'] = '#/parameters/' . $key;
          }
          else {
            // Set source key on a non-global parameter.
            $parameter['_dp_source_key'] = $parent_source_key . '/parameters/form_data/' . $parameter['name'];
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_form_data_params'])) {
      $data['api_form_data_params'] = [];
    }

    if (($field_name === 'parameters') && is_array($node)) {
      foreach ($node as $key => &$parameter) {
        if (!empty($parameter['in']) && ($parameter['in'] === 'formData')) {
          // This can be either a global or a non-global parameter.
          $data['api_form_data_params'][$parameter['_dp_source_key']] = [
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

    if (empty($data['api_doc']) || empty($data['api_form_data_params'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $form_data_params = [];
    foreach ($data['api_form_data_params'] as $source_key => $item) {
      $form_data_param = $item['node'];

      $items = [];
      if (!empty($form_data_param['items'])) {
        $items[] = [$this->apiRefID, $source_key . '/items'];
      }

      $form_data_params[] = [
        'name' => $form_data_param['name'],
        'description' => empty($form_data_param['description']) ? '' : $form_data_param['description'],
        'required' => empty($form_data_param['required']) ? '' : $form_data_param['required'],
        'allow_empty_value' => empty($form_data_param['allowEmptyValue']) ? '' : $form_data_param['allowEmptyValue'],
        'type' => $form_data_param['type'],
        'format' => empty($form_data_param['format']) ? '' : $form_data_param['format'],
        'collection_format' => empty($form_data_param['collectionFormat']) ? '' : $form_data_param['collectionFormat'],
        'default' => empty($form_data_param['default']) ? '' : $form_data_param['default'],
        'items' => $items,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($form_data_params);
  }

}
