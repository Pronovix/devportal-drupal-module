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
 * Provides an APIParamItem deletion confirmation form.
 */
class DeleteMultipleAPIParamItems extends ConfirmFormBase {

  /**
   * The array of APIParamItems to delete.
   *
   * @var string[][]
   */
  protected $apiParamItemInfo = [];

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
   * The APIParamItem storage.
   *
   * @var \Drupal\devportal_api_entities\APIParamItemStorageInterface
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
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityTypeManagerInterface $manager, AccountProxyInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->storage = $manager->getStorage('api_param_item');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
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
    return 'api_param_item_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiParamItemInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_param_item.collection');
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
    $this->apiParamItemInfo = $this->tempStoreFactory->get('api_param_item_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiParamItemInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_entities\APIParamItemInterface[] $api_param_items */
    $api_param_items = $this->storage->loadMultiple(array_keys($this->apiParamItemInfo));

    $items = [];
    foreach ($this->apiParamItemInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_param_item = $api_param_items[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_param_item->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_param_item->getTranslationLanguages();
        if (count($languages) > 1 && $api_param_item->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API HTTP Method Parameter Item translations will be deleted:</em>', ['@label' => $api_param_item->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_param_item->label();
        }
      }
    }

    $form['api_param_items'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->apiParamItemInfo)) {
      $total_count = 0;
      $delete_api_param_items = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_entities\APIParamItemInterface[] $api_param_items */
      $api_param_items = $this->storage->loadMultiple(array_keys($this->apiParamItemInfo));

      foreach ($this->apiParamItemInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_param_item = $api_param_items[$id]->getTranslation($langcode);
          if ($api_param_item->isDefaultTranslation()) {
            $delete_api_param_items[$id] = $api_param_item;
            unset($delete_translations[$id]);
            $total_count += count($api_param_item->getTranslationLanguages());
          }
          elseif (!isset($delete_api_param_items[$id])) {
            $delete_translations[$id][] = $api_param_item;
          }
        }
      }

      if ($delete_api_param_items) {
        $this->storage->delete($delete_api_param_items);
        $this->logger('api_param_item')->notice('Deleted @count API HTTP Method Parameter Items.', ['@count' => count($delete_api_param_items)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_param_item = $api_param_items[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_param_item->removeTranslation($translation->language()->getId());
          }
          $api_param_item->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_param_item')->notice('Deleted @count API HTTP Method Parameter Item translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        drupal_set_message($this->formatPlural($total_count, 'Deleted 1 API HTTP Method Parameter Item.', 'Deleted @count API HTTP Method Parameter Items.'));
      }

      $this->tempStoreFactory->get('api_param_item_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_param_item.collection');
  }

}
