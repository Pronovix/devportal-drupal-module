<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API License entity type.
 */
class APILicenseViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_license']['api_license_bulk_form'] = [
      'title' => $this->t('API License operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Licenses.'),
      'field' => [
        'id' => 'api_license_bulk_form',
      ],
    ];

    return $data;
  }

}
