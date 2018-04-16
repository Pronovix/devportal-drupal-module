<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Header Param entity type.
 */
class APIHeaderParamViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_header_param']['api_header_param_bulk_form'] = [
      'title' => $this->t('API HTTP Method Header Parameter operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API HTTP Method Header Parameters.'),
      'field' => [
        'id' => 'api_header_param_bulk_form',
      ],
    ];

    return $data;
  }

}
