<?php

namespace Drupal\devportal_api_entities\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\devportal_api_entities\APIResponseSetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting an APIResponseSet revision for a single translation.
 *
 * @ingroup devportal_api_entities
 */
class APIResponseSetRevisionRevertTranslationForm extends APIResponseSetRevisionRevertForm {

  /**
   * The language to be reverted.
   *
   * @var string
   */
  protected $langcode;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new APIResponseSetRevisionRevertTranslationForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The APIResponseSet storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time_service
   *   The time service.
   */
  public function __construct(EntityStorageInterface $entity_storage, DateFormatterInterface $date_formatter, LanguageManagerInterface $language_manager, TimeInterface $time_service) {
    parent::__construct($entity_storage, $date_formatter, $time_service);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $entity_storage */
    $entity_storage = $container->get('entity_type.manager')
      ->getStorage('api_response_set');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = $container->get('language_manager');
    /** @var \Drupal\Component\Datetime\TimeInterface $time_service */
    $time_service = $container->get('datetime.time');
    return new static(
      $entity_storage,
      $date_formatter,
      $language_manager,
      $time_service
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_response_set_revision_revert_translation_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert @language translation to the revision from %revision-date?', [
      '@language' => $this->languageManager->getLanguageName($this->langcode),
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $api_response_set_revision = NULL, $langcode = NULL) {
    $this->langcode = $langcode;
    $form = parent::buildForm($form, $form_state, $api_response_set_revision);

    $form['revert_untranslated_fields'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Revert content shared among translations'),
      '#default_value' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareRevertedRevision(APIResponseSetInterface $revision, FormStateInterface $form_state) {
    $revert_untranslated_fields = $form_state->getValue('revert_untranslated_fields');

    /** @var \Drupal\devportal_api_entities\APIResponseSetInterface $latest_revision */
    $latest_revision = $this->apiResponseSetStorage->load($revision->id());
    $latest_revision_translation = $latest_revision->getTranslation($this->langcode);

    $revision_translation = $revision->getTranslation($this->langcode);

    foreach ($latest_revision_translation->getFieldDefinitions() as $field_name => $definition) {
      if ($definition->isTranslatable() || $revert_untranslated_fields) {
        $latest_revision_translation->set($field_name, $revision_translation->get($field_name)->getValue());
      }
    }

    $latest_revision_translation->setNewRevision();
    $latest_revision_translation->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime($this->timeService->getRequestTime());

    return $latest_revision_translation;
  }

}
