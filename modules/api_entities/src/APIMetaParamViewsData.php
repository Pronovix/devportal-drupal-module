<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Meta Parameter entity type.
 */
class APIMetaParamViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_meta_param']['api_meta_param_bulk_form'] = [
      'title' => $this->t('API Meta Parameter operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Meta Parameters.'),
      'field' => [
        'id' => 'api_meta_param_bulk_form',
      ],
    ];

    return $data;
  }

}
