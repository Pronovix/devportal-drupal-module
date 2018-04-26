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
 * Provides an APIExtDoc deletion confirmation form.
 */
class DeleteMultipleAPIExtDocs extends ConfirmFormBase {

  /**
   * The array of APIExtDocs to delete.
   *
   * @var string[][]
   */
  protected $apiExtDocInfo = [];

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
   * The APIExtDoc storage.
   *
   * @var \Drupal\devportal_api_entities\APIExtDocStorageInterface
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
    $this->storage = $manager->getStorage('api_ext_doc');
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
    return 'api_ext_doc_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiExtDocInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_ext_doc.collection');
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
    $this->apiExtDocInfo = $this->tempStoreFactory->get('api_ext_doc_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiExtDocInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_entities\APIExtDocInterface[] $api_ext_docs */
    $api_ext_docs = $this->storage->loadMultiple(array_keys($this->apiExtDocInfo));

    $items = [];
    foreach ($this->apiExtDocInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_ext_doc = $api_ext_docs[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_ext_doc->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_ext_doc->getTranslationLanguages();
        if (count($languages) > 1 && $api_ext_doc->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API External Documentation translations will be deleted:</em>', ['@label' => $api_ext_doc->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_ext_doc->label();
        }
      }
    }

    $form['api_ext_docs'] = [
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
    if ($form_state->getValue('confirm') && !empty($this->apiExtDocInfo)) {
      $total_count = 0;
      $delete_api_ext_docs = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_entities\APIExtDocInterface[] $api_ext_docs */
      $api_ext_docs = $this->storage->loadMultiple(array_keys($this->apiExtDocInfo));

      foreach ($this->apiExtDocInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_ext_doc = $api_ext_docs[$id]->getTranslation($langcode);
          if ($api_ext_doc->isDefaultTranslation()) {
            $delete_api_ext_docs[$id] = $api_ext_doc;
            unset($delete_translations[$id]);
            $total_count += count($api_ext_doc->getTranslationLanguages());
          }
          elseif (!isset($delete_api_ext_docs[$id])) {
            $delete_translations[$id][] = $api_ext_doc;
          }
        }
      }

      if ($delete_api_ext_docs) {
        $this->storage->delete($delete_api_ext_docs);
        $this->logger('api_ext_doc')->notice('Deleted @count API External Documentations.', ['@count' => count($delete_api_ext_docs)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_ext_doc = $api_ext_docs[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_ext_doc->removeTranslation($translation->language()->getId());
          }
          $api_ext_doc->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_ext_doc')->notice('Deleted @count API External Documentation translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        $this->messenger()->addMessage($this->formatPlural($total_count, 'Deleted 1 API External Documentation.', 'Deleted @count API External Documentations.'));
      }

      $this->tempStoreFactory->get('api_ext_doc_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_ext_doc.collection');
  }

}
