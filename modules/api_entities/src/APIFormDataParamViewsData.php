<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Form Data Param entity type.
 */
class APIFormDataParamViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_form_data_param']['api_form_data_param_bulk_form'] = [
      'title' => $this->t('API HTTP Method Form Data Parameter operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API HTTP Method Form Data Parameters.'),
      'field' => [
        'id' => 'api_form_data_param_bulk_form',
      ],
    ];

    return $data;
  }

}
