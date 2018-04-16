<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\system\Plugin\views\field\BulkForm;

/**
 * Defines an API Endpoint operations bulk form element.
 *
 * @ViewsField("api_endpoint_bulk_form")
 */
class APIEndpointBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Endpoint selected.');
  }

}
