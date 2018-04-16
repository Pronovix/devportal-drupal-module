<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Contact entity type.
 */
class APIContactViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_contact']['api_contact_bulk_form'] = [
      'title' => $this->t('API Contact operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Contacts.'),
      'field' => [
        'id' => 'api_contact_bulk_form',
      ],
    ];

    return $data;
  }

}
