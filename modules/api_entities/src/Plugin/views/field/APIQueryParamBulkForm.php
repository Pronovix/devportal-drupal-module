<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\system\Plugin\views\field\BulkForm;

/**
 * Defines an API Query Param operations bulk form element.
 *
 * @ViewsField("api_query_param_bulk_form")
 */
class APIQueryParamBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API HTTP Method Query Parameter selected.');
  }

}
