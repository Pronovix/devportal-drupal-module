<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\source;

/**
 * Retrieves data for a Swagger 2.0 type API Tag entity.
 *
 * @MigrateSource(
 *   id = "dp_api_tag_swagger_20"
 * )
 */
class APITagSwagger20 extends Swagger20Source implements Swagger20SourceInterface {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => t('Name'),
      'description' => t('Description'),
      'ext_doc' => t('External documentation'),
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
      'name' => [
        'type' => 'string',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function setSourceNodeKey(array &$node, $field_name, $path, $parent_source_key) {
    if (($path === '/') && isset($node['tags'])) {
      foreach ($node['tags'] as &$tag) {
        $tag['_dp_source_key'] = $tag['name'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function collectSourceNodeData(array &$node, $field_name, $path, $parent_source_key, array &$data) {
    // Initialize $data.
    if (!isset($data['api_tags'])) {
      $data['api_tags'] = [];
    }

    // Collect tag definitions from the root Swagger object.
    if (($path === '/') && isset($node['tags'])) {
      foreach ($node['tags'] as &$tag) {
        $data['api_tags'][$tag['_dp_source_key']] = [
          'node' => $tag,
        ];
      }
    }

    // Look for tags on Swagger Operation objects.
    $http_methods = 'get|put|post|delete|options|head|patch';
    if (preg_match("/^\/paths\/.*\/($http_methods)\/$/", $path) && !empty($node['tags'])) {
      foreach ($node['tags'] as $tag_name) {
        // Check whether this tag is defined in the root Swagger object or not.
        $defined = FALSE;
        foreach ($data['api_tags'] as $api_tag) {
          if ($api_tag['node']['name'] === $tag_name) {
            $defined = TRUE;
            break;
          }
        }
        // If the tag is not defined in the root Swagger object add it to the
        // list of defined tags. These kind of tags don't have source keys.
        if (!$defined) {
          $data['api_tags'][] = [
            'node' => [
              'name' => $tag_name,
            ],
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

    if (empty($data['api_doc']) || empty($data['api_tags'])) {
      return new \ArrayIterator([]);
    }

    $api_version = [[$this->apiRefID, $data['api_doc']['node']['info']['version']]];

    $tags = [];
    foreach ($data['api_tags'] as $item) {
      $tag = $item['node'];

      $ext_doc = [];
      if (!empty($tag['externalDocs'])) {
        $ext_doc[] = [$this->apiRefID, $tag['externalDocs']['_dp_source_key']];
      }

      $extensions = [];
      foreach ($tag as $field_name => $value) {
        if (substr($field_name, 0, 2) === 'x-') {
          $extensions[] = ['name' => $field_name, 'value' => json_encode($value)];
        }
      }

      $tags[] = [
        'name' => $tag['name'],
        'description' => empty($tag['description']) ? '' : $tag['description'],
        'ext_doc' => $ext_doc,
        'extensions' => $extensions,
        'api_version' => $api_version,
        'api_ref_id' => $this->apiRefID,
      ];
    }

    return new \ArrayIterator($tags);
  }

}
