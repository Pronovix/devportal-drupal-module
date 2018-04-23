<?php

namespace Drupal\devportal_api_reference\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides an APIRef deletion confirmation form.
 */
class DeleteMultipleAPIRefs extends ConfirmFormBase {

  /**
   * The array of APIRefs to delete.
   *
   * @var string[][]
   */
  protected $apiRefInfo = [];

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
   * The APIRef storage.
   *
   * @var \Drupal\devportal_api_reference\APIRefStorageInterface
   */
  protected $storage;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   The entity manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityTypeManagerInterface $manager, AccountProxyInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->storage = $manager->getStorage('api_ref');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\user\PrivateTempStoreFactory $temp_store_factory */
    $temp_store_factory = $container->get('user.private_tempstore');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $manager */
    $manager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Session\AccountProxyInterface $current_user */
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
    return 'api_ref_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiRefInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_ref.collection');
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
    $this->apiRefInfo = $this->tempStoreFactory->get('api_ref_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiRefInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_reference\APIRefInterface[] $api_refs */
    $api_refs = $this->storage->loadMultiple(array_keys($this->apiRefInfo));

    $items = [];
    foreach ($this->apiRefInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_ref = $api_refs[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_ref->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_ref->getTranslationLanguages();
        if (count($languages) > 1 && $api_ref->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API Reference translations will be deleted:</em>', ['@label' => $api_ref->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_ref->label();
        }
      }
    }

    $form['api_refs'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->apiRefInfo)) {
      $total_count = 0;
      $delete_api_refs = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_reference\APIRefInterface[] $api_refs */
      $api_refs = $this->storage->loadMultiple(array_keys($this->apiRefInfo));

      foreach ($this->apiRefInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_ref = $api_refs[$id]->getTranslation($langcode);
          if ($api_ref->isDefaultTranslation()) {
            $delete_api_refs[$id] = $api_ref;
            unset($delete_translations[$id]);
            $total_count += count($api_ref->getTranslationLanguages());
          }
          elseif (!isset($delete_api_refs[$id])) {
            $delete_translations[$id][] = $api_ref;
          }
        }
      }

      if ($delete_api_refs) {
        $this->storage->delete($delete_api_refs);
        $this->logger('api_ref')->notice('Deleted @count API References.', ['@count' => count($delete_api_refs)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_ref = $api_refs[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_ref->removeTranslation($translation->language()->getId());
          }
          $api_ref->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_ref')->notice('Deleted @count API Reference translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        drupal_set_message($this->formatPlural($total_count, 'Deleted 1 API Reference.', 'Deleted @count API References.'));
      }

      $this->tempStoreFactory->get('api_ref_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_ref.collection');
  }

}
