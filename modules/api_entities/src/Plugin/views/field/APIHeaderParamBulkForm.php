<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Header Param operations bulk form element.
 *
 * @ViewsField("api_header_param_bulk_form")
 */
class APIHeaderParamBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API HTTP Method Header Parameter selected.');
  }

}
