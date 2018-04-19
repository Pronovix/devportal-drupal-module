<?php

namespace Drupal\devportal_api_bundle\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Generic form controller for the DHL content entity forms.
 */
class DHLAPIEntitiesContentEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity_type_id  = $this->entity->getEntityTypeId();
    $bundle_info = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
    $bundle_label = $bundle_info[$this->entity->bundle()]['label'];

    $form['footer'] = [
      '#type' => 'container',
      '#weight' => 99,
      '#attributes' => [
        'class' => [$entity_type_id . '-form-footer']
      ]
    ];
    $form['status']['#group'] = 'footer';

    switch ($this->operation) {
      case 'edit':
        $form['#title'] = $this->t('Edit %bundle %label', [
          '%bundle' => $bundle_label,
          '%label' => $this->entity->label(),
        ]);
        break;

      case 'add':
        $form['#title'] = $this->t('Add %bundle', ['%bundle' => $bundle_label]);
        break;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $insert = $this->entity->isNew();
    $this->entity->save();

    $t_args = [
      '@type' => $this->entity->getEntityType()->getLabel(),
      '%label' => $this->entity->label(),
    ];
    $entity_type_id  = $this->entity->getEntityTypeId();

    if ($insert) {
      drupal_set_message($this->t('@type %label has been created.', $t_args));
    }
    else {
      drupal_set_message($this->t('@type %label has been updated.', $t_args));
    }

    if ($this->entity->id()) {
      $form_state->setValue('id', $this->entity->id());
      $form_state->set('id', $this->entity->id());
      if ($this->entity->access('view')) {
        $form_state->setRedirect(
          'entity.' . $entity_type_id . '.canonical',
          [$entity_type_id => $this->entity->id()]
        );
      }
      else {
        $form_state->setRedirect('<front>');
      }
    }
    else {
      // In the unlikely case something went wrong on save, the entity will be
      // rebuilt and its form will be redisplayed.
      drupal_set_message($this->t('The @type could not be saved.', $t_args), 'error');
      $form_state->setRebuild();
    }
  }

}
