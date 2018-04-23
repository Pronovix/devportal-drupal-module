<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Doc operations bulk form element.
 *
 * @ViewsField("api_doc_bulk_form")
 */
class APIDocBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Documentation selected.');
  }

}
