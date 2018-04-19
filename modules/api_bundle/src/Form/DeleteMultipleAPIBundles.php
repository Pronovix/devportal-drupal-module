<?php

namespace Drupal\devportal_api_bundle\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides an APIBundle deletion confirmation form.
 */
class DeleteMultipleAPIBundles extends ConfirmFormBase {

  /**
   * The array of APIBundles to delete.
   *
   * @var string[][]
   */
  protected $apiBundleInfo = [];

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
   * The APIBundle storage.
   *
   * @var \Drupal\devportal_api_bundle\APIBundleStorageInterface
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
    $this->storage = $manager->getStorage('api_bundle');
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
    return 'api_bundle_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiBundleInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_bundle.collection');
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
    $this->apiBundleInfo = $this->tempStoreFactory->get('api_bundle_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiBundleInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_bundle\APIBundleInterface[] $api_bundles */
    $api_bundles = $this->storage->loadMultiple(array_keys($this->apiBundleInfo));

    $items = [];
    foreach ($this->apiBundleInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_bundle = $api_bundles[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_bundle->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_bundle->getTranslationLanguages();
        if (count($languages) > 1 && $api_bundle->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API Bundle translations will be deleted:</em>', ['@label' => $api_bundle->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_bundle->label();
        }
      }
    }

    $form['api_bundles'] = [
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
    if ($form_state->getValue('confirm') && !empty($this->apiBundleInfo)) {
      $total_count = 0;
      $delete_api_bundles = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_bundle\APIBundleInterface[] $api_bundles */
      $api_bundles = $this->storage->loadMultiple(array_keys($this->apiBundleInfo));

      foreach ($this->apiBundleInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_bundle = $api_bundles[$id]->getTranslation($langcode);
          if ($api_bundle->isDefaultTranslation()) {
            $delete_api_bundles[$id] = $api_bundle;
            unset($delete_translations[$id]);
            $total_count += count($api_bundle->getTranslationLanguages());
          }
          elseif (!isset($delete_api_bundles[$id])) {
            $delete_translations[$id][] = $api_bundle;
          }
        }
      }

      if ($delete_api_bundles) {
        $this->storage->delete($delete_api_bundles);
        $this->logger('api_bundle')->notice('Deleted @count API Bundles.', ['@count' => count($delete_api_bundles)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_bundle = $api_bundles[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_bundle->removeTranslation($translation->language()->getId());
          }
          $api_bundle->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_bundle')->notice('Deleted @count API Bundle translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        drupal_set_message($this->formatPlural($total_count, 'Deleted 1 API Bundle.', 'Deleted @count API Bundles.'));
      }

      $this->tempStoreFactory->get('api_bundle_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_bundle.collection');
  }

}
