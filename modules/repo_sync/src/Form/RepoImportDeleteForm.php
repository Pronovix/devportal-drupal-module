<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class RepoImportDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure that you want to delete %name?', [
      '%name' => $this->entity->getLabel(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('By deleting a repository import, every imported content will be removed from Drupal. Are you sure you want to delete repository import %label?', [
      '%label' => $this->entity->getLabel(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.repo_import.collection');
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    drupal_set_message($this->t('Repository import %label has been deleted.', [
      '%label' => $this->entity->getLabel(),
    ]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
