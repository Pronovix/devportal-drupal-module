<?php

namespace Drupal\devportal_api_reference\Plugin\Reference;

/**
 * @Reference(
 *   id = "openapi3",
 *   label = @Translation("OpenAPI 3"),
 *   extensions = { "yml", "yaml", "json" },
 *   weight = 1,
 * )
 */
class OpenApi3 extends OpenApi {

  /**
   * {@inheritdoc}
   */
  protected function getSchema(): string {
    return drupal_get_path('module', 'devportal_api_reference') . '/data/openapi30-schema.json';
  }

  /**
   * {@inheritdoc}
   */
  protected function isValid(\stdClass $data): bool {
    return ($data->openapi ?? NULL) === '3.0.0';
  }

}
