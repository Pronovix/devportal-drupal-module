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
 * Provides an APIResponseSet deletion confirmation form.
 */
class DeleteMultipleAPIResponseSets extends ConfirmFormBase {

  /**
   * The array of APIResponseSets to delete.
   *
   * @var string[][]
   */
  protected $apiResponseSetInfo = [];

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
   * The APIResponseSet storage.
   *
   * @var \Drupal\devportal_api_entities\APIResponseSetStorageInterface
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
    $this->storage = $manager->getStorage('api_response_set');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function create(ContainerInterface $container) {
    /** @var PrivateTempStoreFactory $temp_store_factory */
    $temp_store_factory = $container->get('tempstore.private');
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
    return 'api_response_set_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiResponseSetInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_response_set.collection');
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
    $this->apiResponseSetInfo = $this->tempStoreFactory->get('api_response_set_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiResponseSetInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_entities\APIResponseSetInterface[] $api_response_sets */
    $api_response_sets = $this->storage->loadMultiple(array_keys($this->apiResponseSetInfo));

    $items = [];
    foreach ($this->apiResponseSetInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_response_set = $api_response_sets[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_response_set->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_response_set->getTranslationLanguages();
        if (count($languages) > 1 && $api_response_set->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API Response Set translations will be deleted:</em>', ['@label' => $api_response_set->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_response_set->label();
        }
      }
    }

    $form['api_response_sets'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->apiResponseSetInfo)) {
      $total_count = 0;
      $delete_api_response_sets = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_entities\APIResponseSetInterface[] $api_response_sets */
      $api_response_sets = $this->storage->loadMultiple(array_keys($this->apiResponseSetInfo));

      foreach ($this->apiResponseSetInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_response_set = $api_response_sets[$id]->getTranslation($langcode);
          if ($api_response_set->isDefaultTranslation()) {
            $delete_api_response_sets[$id] = $api_response_set;
            unset($delete_translations[$id]);
            $total_count += count($api_response_set->getTranslationLanguages());
          }
          elseif (!isset($delete_api_response_sets[$id])) {
            $delete_translations[$id][] = $api_response_set;
          }
        }
      }

      if ($delete_api_response_sets) {
        $this->storage->delete($delete_api_response_sets);
        $this->logger('api_response_set')->notice('Deleted @count API Response Sets.', ['@count' => count($delete_api_response_sets)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_response_set = $api_response_sets[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_response_set->removeTranslation($translation->language()->getId());
          }
          $api_response_set->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_response_set')->notice('Deleted @count API Response Set translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        $this->messenger()->addMessage($this->formatPlural($total_count, 'Deleted 1 API Response Set.', 'Deleted @count API Response Sets.'));
      }

      $this->tempStoreFactory->get('api_response_set_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_response_set.collection');
  }

}
