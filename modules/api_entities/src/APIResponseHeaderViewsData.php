<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Response Header entity type.
 */
class APIResponseHeaderViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_response_header']['api_response_header_bulk_form'] = [
      'title' => $this->t('API Response Header operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Response Headers.'),
      'field' => [
        'id' => 'api_response_header_bulk_form',
      ],
    ];

    return $data;
  }

}
