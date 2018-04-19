<?php

namespace Drupal\devportal_api_bundle;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Bundle entity type.
 */
class APIBundleViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['api_bundle']['api_bundle_bulk_form'] = [
      'title' => $this->t('API Bundle operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Bundles.'),
      'field' => [
        'id' => 'api_bundle_bulk_form',
      ],
    ];

    return $data;
  }

}
