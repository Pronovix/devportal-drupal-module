<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines an API Meta Parameter operations bulk form element.
 *
 * @ViewsField("api_meta_param_bulk_form")
 */
class APIMetaParamBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Meta Parameter selected.');
  }

}
