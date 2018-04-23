<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Endpoint Set operations bulk form element.
 *
 * @ViewsField("api_endpoint_set_bulk_form")
 */
class APIEndpointSetBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Endpoint Set selected.');
  }

}
