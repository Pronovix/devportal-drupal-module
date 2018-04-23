<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Form Data Param operations bulk form element.
 *
 * @ViewsField("api_form_data_param_bulk_form")
 */
class APIFormDataParamBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API HTTP Method Form Data Parameter selected.');
  }

}
