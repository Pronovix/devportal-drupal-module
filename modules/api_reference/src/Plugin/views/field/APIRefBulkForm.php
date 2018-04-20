<?php

namespace Drupal\devportal_api_reference\Plugin\views\field;

use \Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Reference operations bulk form element.
 *
 * @ViewsField("api_ref_bulk_form")
 */
class APIRefBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Reference selected.');
  }

}
