<?php

namespace Drupal\devportal_api_entities\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\devportal_api_entities\APIDocInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting an APIDoc revision.
 *
 * @ingroup devportal_api_entities
 */
class APIDocRevisionRevertForm extends ConfirmFormBase {

  /**
   * The APIDoc revision.
   *
   * @var \Drupal\devportal_api_entities\APIDocInterface
   */
  protected $revision;

  /**
   * The APIDoc storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $apiDocStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $timeService;

  /**
   * Constructs a new APIDocRevisionRevertForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The APIDoc storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Component\Datetime\TimeInterface $time_service
   *   The time service.
   */
  public function __construct(EntityStorageInterface $entity_storage, DateFormatterInterface $date_formatter, TimeInterface $time_service) {
    $this->apiDocStorage = $entity_storage;
    $this->dateFormatter = $date_formatter;
    $this->timeService = $time_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_storage = $container->get('entity.manager')
      ->getStorage('api_doc');
    /** @var DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    /** @var TimeInterface $time_service */
    $time_service = $container->get('datetime.time');
    return new static(
      $entity_storage,
      $date_formatter,
      $time_service
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_doc_revision_revert_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert to the revision from %revision-date?', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_doc.version_history', ['api_doc' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Revert');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $api_doc_revision = NULL) {
    $this->revision = $this->apiDocStorage->loadRevision($api_doc_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The revision timestamp will be updated when the revision is saved. Keep
    // the original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision = $this->prepareRevertedRevision($this->revision, $form_state);
    $this->revision->setRevisionLogMessage($this->t('Copy of the revision from %date.', [
      '%date' => $this->dateFormatter->format($original_revision_timestamp),
    ]));
    $this->revision->save();

    $this->logger('content')->notice('API Documentation: reverted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    drupal_set_message($this->t('API Documentation %title has been reverted to the revision from %revision-date.', [
      '%title' => $this->revision->label(),
      '%revision-date' => $this->dateFormatter->format($original_revision_timestamp),
    ]));
    $form_state->setRedirect(
      'entity.api_doc.version_history',
      ['api_doc' => $this->revision->id()]
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\devportal_api_entities\APIDocInterface $revision
   *   The revision to be reverted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\devportal_api_entities\APIDocInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(APIDocInterface $revision, FormStateInterface $form_state) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime($this->timeService->getRequestTime());

    return $revision;
  }

}
