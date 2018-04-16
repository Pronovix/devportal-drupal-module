<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Parameter entity type.
 */
class APIParamViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_param']['api_param_bulk_form'] = [
      'title' => $this->t('API Parameter operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Parameters.'),
      'field' => [
        'id' => 'api_param_bulk_form',
      ],
    ];

    return $data;
  }

}
