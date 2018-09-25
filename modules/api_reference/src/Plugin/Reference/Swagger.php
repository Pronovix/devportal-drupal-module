<?php

namespace Drupal\devportal_api_reference\Plugin\Reference;

/**
 * @Reference(
 *   id = "swagger",
 *   label = @Translation("Swagger (OpenAPI 2)"),
 *   extensions = { "yml", "yaml", "json" },
 *   weight = 2,
 * )
 */
class Swagger extends OpenApi {

  /**
   * {@inheritdoc}
   */
  protected function getSchema(): string {
    return drupal_get_path('module', 'devportal_api_reference') . '/data/openapi20-schema.json';
  }

  /**
   * {@inheritdoc}
   */
  protected function isValid(\stdClass $data): bool {
    return ($data->swagger ?? NULL) === '2.0';
  }

}
