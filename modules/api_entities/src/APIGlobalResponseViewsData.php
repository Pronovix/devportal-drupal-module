<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Global Response entity type.
 */
class APIGlobalResponseViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_global_response']['api_global_response_bulk_form'] = [
      'title' => $this->t('API Global Response operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Global Responses.'),
      'field' => [
        'id' => 'api_global_response_bulk_form',
      ],
    ];

    return $data;
  }

}
