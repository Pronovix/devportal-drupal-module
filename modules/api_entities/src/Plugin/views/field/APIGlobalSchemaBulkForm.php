<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Global Schema operations bulk form element.
 *
 * @ViewsField("api_global_schema_bulk_form")
 */
class APIGlobalSchemaBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Global Schema selected.');
  }

}
