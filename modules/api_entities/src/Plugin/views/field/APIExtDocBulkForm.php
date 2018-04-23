<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Ext Doc operations bulk form element.
 *
 * @ViewsField("api_ext_doc_bulk_form")
 */
class APIExtDocBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API External Documentation selected.');
  }

}
