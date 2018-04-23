<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Path Param operations bulk form element.
 *
 * @ViewsField("api_path_param_bulk_form")
 */
class APIPathParamBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API HTTP Method Path Parameter selected.');
  }

}
