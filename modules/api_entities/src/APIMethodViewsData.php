<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Method entity type.
 */
class APIMethodViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Change the relationship label/title to a bit more meaningful one
    // (original was "Taxonomy term" for both Produces and Consumes fields).
    $data['api_method__produces']['produces_target_id']['relationship']['label'] = $this->t('Produces taxonomy term');
    $data['api_method__produces']['produces_target_id']['relationship']['title'] = $this->t('Produces taxonomy term');
    $data['api_method__consumes']['consumes_target_id']['relationship']['label'] = $this->t('Consumes taxonomy term');
    $data['api_method__consumes']['consumes_target_id']['relationship']['title'] = $this->t('Consumes taxonomy term');

    $data['api_method']['api_method_bulk_form'] = [
      'title' => $this->t('API HTTP Method operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API HTTP Methods.'),
      'field' => [
        'id' => 'api_method_bulk_form',
      ],
    ];

    return $data;
  }

}
