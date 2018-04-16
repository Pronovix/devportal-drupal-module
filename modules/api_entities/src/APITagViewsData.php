<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Tag entity type.
 */
class APITagViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_tag']['api_tag_bulk_form'] = [
      'title' => $this->t('API Tag operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Tags.'),
      'field' => [
        'id' => 'api_tag_bulk_form',
      ],
    ];

    return $data;
  }

}
