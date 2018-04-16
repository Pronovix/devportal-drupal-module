<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Endpoint Set entity type.
 */
class APIEndpointSetViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_endpoint_set']['api_endpoint_set_bulk_form'] = [
      'title' => $this->t('API Endpoint Set operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Endpoint Sets.'),
      'field' => [
        'id' => 'api_endpoint_set_bulk_form',
      ],
    ];

    return $data;
  }

}
