<?php

namespace Drupal\devportal_api_entities\Plugin\views\field;

use Drupal\system\Plugin\views\field\BulkForm;

/**
 * Defines an API Param Item operations bulk form element.
 *
 * @ViewsField("api_param_item_bulk_form")
 */
class APIParamItemBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No API HTTP Method Parameter Item selected.');
  }

}
