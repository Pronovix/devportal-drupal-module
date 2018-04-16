<?php

namespace Drupal\devportal_api_entities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\devportal\Form\DevportalContentEntityForm;

/**
 * Form controller for the API Version Tag content entity.
 */
class APIVersionTagForm extends DevportalContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // We have to add the advanced tab manually because it gets added
    // automatically only in those cases where the content entity type has the
    // show_revision_ui property in its annotation.
    $form['advanced'] = [
      '#type' => 'vertical_tabs',
      '#weight' => 99,
    ];

    $form = parent::form($form, $form_state);

    return $form;
  }

}
