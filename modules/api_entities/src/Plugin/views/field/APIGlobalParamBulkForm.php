<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Global Parameter operations bulk form element.
 *
 * @ViewsField("api_global_param_bulk_form")
 */
class APIGlobalParamBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Global Parameter selected.');
  }

}
