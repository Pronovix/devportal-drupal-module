<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Global Schema entity type.
 */
class APIGlobalSchemaViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_global_schema']['api_global_schema_bulk_form'] = [
      'title' => $this->t('API Global Schema operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Global Schemas.'),
      'field' => [
        'id' => 'api_global_schema_bulk_form',
      ],
    ];

    return $data;
  }

}
