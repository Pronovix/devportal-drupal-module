<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Endpoint entity type.
 */
class APIEndpointViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_endpoint']['api_endpoint_bulk_form'] = [
      'title' => $this->t('API Endpoint operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Endpoints.'),
      'field' => [
        'id' => 'api_endpoint_bulk_form',
      ],
    ];

    return $data;
  }

}
