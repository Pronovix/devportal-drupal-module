<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\system\Plugin\views\field\BulkForm;

/**
 * Defines an API Global Response operations bulk form element.
 *
 * @ViewsField("api_global_response_bulk_form")
 */
class APIGlobalResponseBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Global Response selected.');
  }

}
