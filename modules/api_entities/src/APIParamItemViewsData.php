<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Param Item entity type.
 */
class APIParamItemViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_param_item']['api_param_item_bulk_form'] = [
      'title' => $this->t('API HTTP Method Parameter Item operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API HTTP Method Parameter Items.'),
      'field' => [
        'id' => 'api_param_item_bulk_form',
      ],
    ];

    return $data;
  }

}
