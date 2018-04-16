<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Query Param entity type.
 */
class APIQueryParamViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_query_param']['api_query_param_bulk_form'] = [
      'title' => $this->t('API HTTP Method Query Parameter operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API HTTP Method Query Parameters.'),
      'field' => [
        'id' => 'api_query_param_bulk_form',
      ],
    ];

    return $data;
  }

}
