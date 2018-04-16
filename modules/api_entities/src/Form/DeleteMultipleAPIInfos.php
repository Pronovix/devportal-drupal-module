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
 * Provides an APIInfo deletion confirmation form.
 */
class DeleteMultipleAPIInfos extends ConfirmFormBase {

  /**
   * The array of APIInfos to delete.
   *
   * @var string[][]
   */
  protected $apiInfoInfo = [];

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
   * The APIInfo storage.
   *
   * @var \Drupal\devportal_api_entities\APIInfoStorageInterface
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
    $this->storage = $manager->getStorage('api_info');
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
    return 'api_info_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiInfoInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_info.collection');
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
    $this->apiInfoInfo = $this->tempStoreFactory->get('api_info_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiInfoInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_entities\APIInfoInterface[] $api_infos */
    $api_infos = $this->storage->loadMultiple(array_keys($this->apiInfoInfo));

    $items = [];
    foreach ($this->apiInfoInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_info = $api_infos[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_info->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_info->getTranslationLanguages();
        if (count($languages) > 1 && $api_info->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API Info translations will be deleted:</em>', ['@label' => $api_info->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_info->label();
        }
      }
    }

    $form['api_infos'] = [
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
    if ($form_state->getValue('confirm') && !empty($this->apiInfoInfo)) {
      $total_count = 0;
      $delete_api_infos = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_entities\APIInfoInterface[] $api_infos */
      $api_infos = $this->storage->loadMultiple(array_keys($this->apiInfoInfo));

      foreach ($this->apiInfoInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_info = $api_infos[$id]->getTranslation($langcode);
          if ($api_info->isDefaultTranslation()) {
            $delete_api_infos[$id] = $api_info;
            unset($delete_translations[$id]);
            $total_count += count($api_info->getTranslationLanguages());
          }
          elseif (!isset($delete_api_infos[$id])) {
            $delete_translations[$id][] = $api_info;
          }
        }
      }

      if ($delete_api_infos) {
        $this->storage->delete($delete_api_infos);
        $this->logger('api_info')->notice('Deleted @count API Infos.', ['@count' => count($delete_api_infos)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_info = $api_infos[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_info->removeTranslation($translation->language()->getId());
          }
          $api_info->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_info')->notice('Deleted @count API Info translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        drupal_set_message($this->formatPlural($total_count, 'Deleted 1 API Info.', 'Deleted @count API Infos.'));
      }

      $this->tempStoreFactory->get('api_info_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_info.collection');
  }

}
