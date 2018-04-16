<?php

namespace Drupal\devportal_repo_sync\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\devportal_migrate_batch\Batch\MigrateBatch;
use Drupal\devportal_repo_sync\Entity\RepoImport;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper form for RepoImportViewBuilder.
 */
class RepoImportViewForm extends FormBase {

  /**
   * @var RepoImport
   */
  protected $repoImport;

  /**
   * {@inheritdoc}
   */
  public function __construct(RepoImport $repoImport) {
    $this->repoImport = $repoImport;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var RouteMatchInterface $match */
    $match = $container->get('current_route_match');

    /** @var RepoImport $repoImport */
    $repoImport = $match->getParameter('repo_import');

    return new static($repoImport);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'repo_import_view_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $account = $this->currentUser();

    if ($account->hasPermission('import repository')) {
      $form['import'] = [
        '#type' => 'submit',
        '#value' => $this->t('Import'),
        '#submit' => ['::submitImport'],
      ];
    }

    if ($account->hasPermission('edit repository imports')) {
      $form['edit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Edit'),
        '#submit' => ['::submitEdit'],
      ];
    }

    return $form;
  }

  /**
   * Submit handler that starts the import batch.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitImport(array &$form, FormStateInterface $form_state) {
    MigrateBatch::set($this->repoImport);
  }

  /**
   * Submit handler that goes to the entity edit page.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitEdit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.repo_import.edit_form', [
      'repo_import' => $this->repoImport->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
