<?php

namespace Drupal\devportal_api_entities\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides an API Global Schema deletion confirmation form.
 */
class DeleteMultipleAPIGlobalSchemas extends ConfirmFormBase {

  /**
   * The array of API Global Schemas to delete.
   *
   * @var string[][]
   */
  protected $apiGlobalSchemaInfo = [];

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The API Global Schema storage.
   *
   * @var \Drupal\devportal_api_entities\APIGlobalSchemaStorageInterface
   */
  protected $storage;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   The entity manager.
   * @param \Drupal\Core\Session\AccountProxyInterface
   *   The current user.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityTypeManagerInterface $manager, AccountProxyInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->storage = $manager->getStorage('api_global_schema');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function create(ContainerInterface $container) {
    /** @var PrivateTempStoreFactory $temp_store_factory */
    $temp_store_factory = $container->get('user.private_tempstore');
    /** @var EntityTypeManagerInterface $manager */
    $manager = $container->get('entity_type.manager');
    /** @var AccountProxyInterface $current_user */
    $current_user = $container->get('current_user');
    return new static(
      $temp_store_factory,
      $manager,
      $current_user
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_global_schema_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiGlobalSchemaInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_global_schema.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->apiGlobalSchemaInfo = $this->tempStoreFactory->get('api_global_schema_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiGlobalSchemaInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_entities\APIGlobalSchemaInterface[] $api_global_schemas */
    $api_global_schemas = $this->storage->loadMultiple(array_keys($this->apiGlobalSchemaInfo));

    $items = [];
    foreach ($this->apiGlobalSchemaInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_global_schema = $api_global_schemas[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_global_schema->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_global_schema->getTranslationLanguages();
        if (count($languages) > 1 && $api_global_schema->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API Global Schema translations will be deleted:</em>', ['@label' => $api_global_schema->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_global_schema->label();
        }
      }
    }

    $form['api_global_schemas'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->apiGlobalSchemaInfo)) {
      $total_count = 0;
      $delete_api_global_schemas = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_entities\APIGlobalSchemaInterface[] $api_global_schemas */
      $api_global_schemas = $this->storage->loadMultiple(array_keys($this->apiGlobalSchemaInfo));

      foreach ($this->apiGlobalSchemaInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_global_schema = $api_global_schemas[$id]->getTranslation($langcode);
          if ($api_global_schema->isDefaultTranslation()) {
            $delete_api_global_schemas[$id] = $api_global_schema;
            unset($delete_translations[$id]);
            $total_count += count($api_global_schema->getTranslationLanguages());
          }
          elseif (!isset($delete_api_global_schemas[$id])) {
            $delete_translations[$id][] = $api_global_schema;
          }
        }
      }

      if ($delete_api_global_schemas) {
        $this->storage->delete($delete_api_global_schemas);
        $this->logger('api_global_schema')->notice('Deleted @count API Global Schemas.', ['@count' => count($delete_api_global_schemas)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_global_schema = $api_global_schemas[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_global_schema->removeTranslation($translation->language()->getId());
          }
          $api_global_schema->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_global_schema')->notice('Deleted @count API Global Schema translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        drupal_set_message($this->formatPlural($total_count, 'Deleted 1 API Global Schema.', 'Deleted @count API Global Schemas.'));
      }

      $this->tempStoreFactory->get('api_global_schema_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_global_schema.collection');
  }

}
