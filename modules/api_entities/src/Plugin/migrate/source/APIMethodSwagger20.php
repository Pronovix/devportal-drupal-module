<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Method entity.
 *
 * @MigrateSource(
 *   id = "dp_api_method_swagger_20"
 * )
 */
class APIMethodSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'http_method' => t('HTTP Method'),
      'summary' => t('Summary'),
      'description' => t('Description'),
      'operation_id' => t('Operation ID'),
      'deprecated' => t('Deprecated'),
      'consumes' => t('Consumes'),
      'produces' => t('Produces'),
      'endpoint' => t('Endpoint'),
      'ext_doc' => t('External documentation'),
      'tags' => t('Tags'),
      'params' => t('Parameters'),
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
    $matches = [];
    $http_methods = 'get|put|post|delete|options|head|patch';
    if (preg_match("/^\/paths\/.*\/($http_methods)\/$/", $path, $matches)) {
      $http_method = $matches[1];
      $node['_dp_source_key'] = $parent_source_key . '/' . $http_method;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_methods'])) {
      $data['api_methods'] = [];
    }

    $matches = [];
    $http_methods = 'get|put|post|delete|options|head|patch';
    if (preg_match("/^\/paths\/.*\/($http_methods)\/$/", $path, $matches)) {
      $http_method = $matches[1];
      $data['api_methods'][$node['_dp_source_key']] = [
        'http_method' => $http_method,
        'node' => $node,
        'api_endpoint' => [
          'source_key' => $parent_source_key,
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   */
  public function initializeIterator() {
    $data = $this->getSourceData();

    if (empty($data['api_doc']) || empty($data['api_methods'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $methods = [];
    foreach ($data['api_methods'] as $source_key => $item) {
      $method = $item['node'];
      $http_method = $item['http_method'];
      $api_endpoint = $item['api_endpoint'];

      $consumes = [];
      if (!empty($method['consumes'])) {
        foreach ($method['consumes'] as $mime_type) {
          $consumes[] = [$this->apiRefID, $mime_type];
        }
      }

      $produces = [];
      if (!empty($method['produces'])) {
        foreach ($method['produces'] as $mime_type) {
          $produces[] = [$this->apiRefID, $mime_type];
        }
      }

      $endpoint = [[$this->apiRefID, $api_endpoint['source_key']]];

      $tags = [];
      if (!empty($method['tags'])) {
        foreach ($method['tags'] as $tag_name) {
          $tags[] = [$this->apiRefID, $tag_name];
        }
      }

      $ext_doc = [];
      if (!empty($method['externalDocs'])) {
        $ext_doc[] = [$this->apiRefID, $method['externalDocs']['_dp_source_key']];
      }

      $params = [];
      if (!empty($method['parameters'])) {
        foreach ($method['parameters'] as $parameter) {
          if (!empty($parameter['$ref'])) {
            $params[] = [$this->apiRefID, $parameter['$ref']];
          }
          else if (!empty($parameter['in'])) {
            $params[] = [$this->apiRefID, $parameter['_dp_source_key']];
          }
        }
      }

      $extensions = [];
      foreach ($method as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $methods[] = [
        'http_method' => $http_method,
        'summary' => empty($method['summary']) ? '' : $method['summary'],
        'description' => empty($method['description']) ? '' : $method['description'],
        'operation_id' => empty($method['operationId']) ? '' : $method['operationId'],
        'deprecated' => empty($method['deprecated']) ? 0 : $method['deprecated'],
        'consumes' => $consumes,
        'produces' => $produces,
        'endpoint' => $endpoint,
        'ext_doc' => $ext_doc,
        'tags' => $tags,
        'params' => $params,
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
        'source_key' => $source_key,
      ];
    }

    return new \ArrayIterator($methods);
  }

}
