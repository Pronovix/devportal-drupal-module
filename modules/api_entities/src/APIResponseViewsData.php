<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Response entity type.
 */
class APIResponseViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_response']['api_response_bulk_form'] = [
      'title' => $this->t('API Response operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Responses.'),
      'field' => [
        'id' => 'api_response_bulk_form',
      ],
    ];

    return $data;
  }

}
