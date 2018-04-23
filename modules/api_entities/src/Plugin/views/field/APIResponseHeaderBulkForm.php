<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Response Header operations bulk form element.
 *
 * @ViewsField("api_response_header_bulk_form")
 */
class APIResponseHeaderBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Response Header selected.');
  }

}
