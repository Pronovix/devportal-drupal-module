<?php

namespace Drupal\devportal_api_reference;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Reference entity type.
 */
class APIRefViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_ref']['api_ref_bulk_form'] = [
      'title' => $this->t('API Reference operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API References.'),
      'field' => [
        'id' => 'api_ref_bulk_form',
      ],
    ];

    return $data;
  }

}
