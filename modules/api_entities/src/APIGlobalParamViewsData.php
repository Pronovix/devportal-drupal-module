<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Global Parameter entity type.
 */
class APIGlobalParamViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_global_param']['api_global_param_bulk_form'] = [
      'title' => $this->t('API Global Parameter operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Global Parameters.'),
      'field' => [
        'id' => 'api_global_param_bulk_form',
      ],
    ];

    return $data;
  }

}
