<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Info entity type.
 */
class APIInfoViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_info']['api_info_bulk_form'] = [
      'title' => $this->t('API Info operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Infos.'),
      'field' => [
        'id' => 'api_info_bulk_form',
      ],
    ];

    return $data;
  }

}
