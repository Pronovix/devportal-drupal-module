<?php

namespace Drupal\devportal_api_bundle\Plugin\views\field;

use \Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Bundle operations bulk form element.
 *
 * @ViewsField("api_bundle_bulk_form")
 */
class APIBundleBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Bundle selected.');
  }

}
