<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\system\Plugin\views\field\BulkForm;

/**
 * Defines an API Info operations bulk form element.
 *
 * @ViewsField("api_info_bulk_form")
 */
class APIInfoBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API Info selected.');
  }

}
