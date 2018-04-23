<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Response Example operations bulk form element.
 *
 * @ViewsField("api_response_example_bulk_form")
 */
class APIResponseExampleBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Response Example selected.');
  }

}
