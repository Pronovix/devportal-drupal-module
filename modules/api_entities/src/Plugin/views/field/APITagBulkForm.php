<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Tag operations bulk form element.
 *
 * @ViewsField("api_tag_bulk_form")
 */
class APITagBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Tag selected.');
  }

}
