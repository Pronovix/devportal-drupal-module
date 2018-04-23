<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Version Tag operations bulk form element.
 *
 * @ViewsField("api_version_tag_bulk_form")
 */
class APIVersionTagBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Version Tag selected.');
  }

}
