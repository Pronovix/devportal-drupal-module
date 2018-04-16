<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Version Tag entity type.
 */
class APIVersionTagViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_version_tag']['api_version_tag_bulk_form'] = [
      'title' => $this->t('API Version Tag operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Version Tags.'),
      'field' => [
        'id' => 'api_version_tag_bulk_form',
      ],
    ];

    return $data;
  }

}
