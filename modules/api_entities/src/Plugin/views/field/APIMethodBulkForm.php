<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Method operations bulk form element.
 *
 * @ViewsField("api_method_bulk_form")
 */
class APIMethodBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API HTTP Method selected.');
  }

}
