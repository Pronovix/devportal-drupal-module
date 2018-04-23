<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Schema operations bulk form element.
 *
 * @ViewsField("api_schema_bulk_form")
 */
class APISchemaBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Schema selected.');
  }

}
