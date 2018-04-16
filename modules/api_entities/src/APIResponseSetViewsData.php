<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Response Set entity type.
 */
class APIResponseSetViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_response_set']['api_response_set_bulk_form'] = [
      'title' => $this->t('API Response Set operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Response Sets.'),
      'field' => [
        'id' => 'api_response_set_bulk_form',
      ],
    ];

    return $data;
  }

}
