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
 * Provides an APIResponseExample deletion confirmation form.
 */
class DeleteMultipleAPIResponseExamples extends ConfirmFormBase {

  /**
   * The array of APIResponseExamples to delete.
   *
   * @var string[][]
   */
  protected $apiResponseExampleInfo = [];

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
   * The APIResponseExample storage.
   *
   * @var \Drupal\devportal_api_entities\APIResponseExampleStorageInterface
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
    $this->storage = $manager->getStorage('api_response_example');
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
    return 'api_response_example_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiResponseExampleInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_response_example.collection');
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
    $this->apiResponseExampleInfo = $this->tempStoreFactory->get('api_response_example_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiResponseExampleInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_entities\APIResponseExampleInterface[] $api_response_examples */
    $api_response_examples = $this->storage->loadMultiple(array_keys($this->apiResponseExampleInfo));

    $items = [];
    foreach ($this->apiResponseExampleInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_response_example = $api_response_examples[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_response_example->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_response_example->getTranslationLanguages();
        if (count($languages) > 1 && $api_response_example->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API Response Example translations will be deleted:</em>', ['@label' => $api_response_example->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_response_example->label();
        }
      }
    }

    $form['api_response_examples'] = [
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
    if ($form_state->getValue('confirm') && !empty($this->apiResponseExampleInfo)) {
      $total_count = 0;
      $delete_api_response_examples = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_entities\APIResponseExampleInterface[] $api_response_examples */
      $api_response_examples = $this->storage->loadMultiple(array_keys($this->apiResponseExampleInfo));

      foreach ($this->apiResponseExampleInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_response_example = $api_response_examples[$id]->getTranslation($langcode);
          if ($api_response_example->isDefaultTranslation()) {
            $delete_api_response_examples[$id] = $api_response_example;
            unset($delete_translations[$id]);
            $total_count += count($api_response_example->getTranslationLanguages());
          }
          elseif (!isset($delete_api_response_examples[$id])) {
            $delete_translations[$id][] = $api_response_example;
          }
        }
      }

      if ($delete_api_response_examples) {
        $this->storage->delete($delete_api_response_examples);
        $this->logger('api_response_example')->notice('Deleted @count API Response Examples.', ['@count' => count($delete_api_response_examples)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_response_example = $api_response_examples[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_response_example->removeTranslation($translation->language()->getId());
          }
          $api_response_example->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_response_example')->notice('Deleted @count API Response Example translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        $this->messenger()->addMessage($this->formatPlural($total_count, 'Deleted 1 API Response Example.', 'Deleted @count API Response Examples.'));
      }

      $this->tempStoreFactory->get('api_response_example_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_response_example.collection');
  }

}
