<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Schema entity type.
 */
class APISchemaViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_schema']['api_schema_bulk_form'] = [
      'title' => $this->t('API Schema operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Schemas.'),
      'field' => [
        'id' => 'api_schema_bulk_form',
      ],
    ];

    return $data;
  }

}
