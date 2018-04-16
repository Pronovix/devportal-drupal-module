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
 * Provides an APIQueryParam deletion confirmation form.
 */
class DeleteMultipleAPIQueryParams extends ConfirmFormBase {

  /**
   * The array of APIQueryParams to delete.
   *
   * @var string[][]
   */
  protected $apiQueryParamInfo = [];

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
   * The APIQueryParam storage.
   *
   * @var \Drupal\devportal_api_entities\APIQueryParamStorageInterface
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
    $this->storage = $manager->getStorage('api_query_param');
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
    return 'api_query_param_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiQueryParamInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_query_param.collection');
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
    $this->apiQueryParamInfo = $this->tempStoreFactory->get('api_query_param_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiQueryParamInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_entities\APIQueryParamInterface[] $api_query_params */
    $api_query_params = $this->storage->loadMultiple(array_keys($this->apiQueryParamInfo));

    $items = [];
    foreach ($this->apiQueryParamInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_query_param = $api_query_params[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_query_param->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_query_param->getTranslationLanguages();
        if (count($languages) > 1 && $api_query_param->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API HTTP Method Query Parameter translations will be deleted:</em>', ['@label' => $api_query_param->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_query_param->label();
        }
      }
    }

    $form['api_query_params'] = [
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
    if ($form_state->getValue('confirm') && !empty($this->apiQueryParamInfo)) {
      $total_count = 0;
      $delete_api_query_params = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_entities\APIQueryParamInterface[] $api_query_params */
      $api_query_params = $this->storage->loadMultiple(array_keys($this->apiQueryParamInfo));

      foreach ($this->apiQueryParamInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_query_param = $api_query_params[$id]->getTranslation($langcode);
          if ($api_query_param->isDefaultTranslation()) {
            $delete_api_query_params[$id] = $api_query_param;
            unset($delete_translations[$id]);
            $total_count += count($api_query_param->getTranslationLanguages());
          }
          elseif (!isset($delete_api_query_params[$id])) {
            $delete_translations[$id][] = $api_query_param;
          }
        }
      }

      if ($delete_api_query_params) {
        $this->storage->delete($delete_api_query_params);
        $this->logger('api_query_param')->notice('Deleted @count API HTTP Method Query Parameters.', ['@count' => count($delete_api_query_params)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_query_param = $api_query_params[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_query_param->removeTranslation($translation->language()->getId());
          }
          $api_query_param->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_query_param')->notice('Deleted @count API HTTP Method Query Parameter translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        drupal_set_message($this->formatPlural($total_count, 'Deleted 1 API HTTP Method Query Parameter.', 'Deleted @count API HTTP Method Query Parameters.'));
      }

      $this->tempStoreFactory->get('api_query_param_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_query_param.collection');
  }

}
