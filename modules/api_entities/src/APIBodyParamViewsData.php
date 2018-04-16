<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Body Param entity type.
 */
class APIBodyParamViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_body_param']['api_body_param_bulk_form'] = [
      'title' => $this->t('API HTTP Method Body Parameter operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API HTTP Method Body Parameters.'),
      'field' => [
        'id' => 'api_body_param_bulk_form',
      ],
    ];

    return $data;
  }

}
