<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Path Param entity type.
 */
class APIPathParamViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_path_param']['api_path_param_bulk_form'] = [
      'title' => $this->t('API HTTP Method Path Parameter operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API HTTP Method Path Parameters.'),
      'field' => [
        'id' => 'api_path_param_bulk_form',
      ],
    ];

    return $data;
  }

}
