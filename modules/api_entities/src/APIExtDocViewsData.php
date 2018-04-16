<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Ext Doc entity type.
 */
class APIExtDocViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_ext_doc']['api_ext_doc_bulk_form'] = [
      'title' => $this->t('API External Documentation operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API External Documentations.'),
      'field' => [
        'id' => 'api_ext_doc_bulk_form',
      ],
    ];

    return $data;
  }

}
