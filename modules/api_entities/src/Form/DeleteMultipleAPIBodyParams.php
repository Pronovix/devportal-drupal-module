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
 * Provides an APIBodyParam deletion confirmation form.
 */
class DeleteMultipleAPIBodyParams extends ConfirmFormBase {

  /**
   * The array of APIBodyParams to delete.
   *
   * @var string[][]
   */
  protected $apiBodyParamInfo = [];

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
   * The APIBodyParam storage.
   *
   * @var \Drupal\devportal_api_entities\APIBodyParamStorageInterface
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
    $this->storage = $manager->getStorage('api_body_param');
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
    return 'api_body_param_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->apiBodyParamInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.api_body_param.collection');
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
    $this->apiBodyParamInfo = $this->tempStoreFactory->get('api_body_param_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->apiBodyParamInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\devportal_api_entities\APIBodyParamInterface[] $api_body_params */
    $api_body_params = $this->storage->loadMultiple(array_keys($this->apiBodyParamInfo));

    $items = [];
    foreach ($this->apiBodyParamInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $api_body_param = $api_body_params[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $api_body_param->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $api_body_param->getTranslationLanguages();
        if (count($languages) > 1 && $api_body_param->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following API HTTP Method Body Parameter translations will be deleted:</em>', ['@label' => $api_body_param->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $api_body_param->label();
        }
      }
    }

    $form['api_body_params'] = [
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
    if ($form_state->getValue('confirm') && !empty($this->apiBodyParamInfo)) {
      $total_count = 0;
      $delete_api_body_params = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\devportal_api_entities\APIBodyParamInterface[] $api_body_params */
      $api_body_params = $this->storage->loadMultiple(array_keys($this->apiBodyParamInfo));

      foreach ($this->apiBodyParamInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $api_body_param = $api_body_params[$id]->getTranslation($langcode);
          if ($api_body_param->isDefaultTranslation()) {
            $delete_api_body_params[$id] = $api_body_param;
            unset($delete_translations[$id]);
            $total_count += count($api_body_param->getTranslationLanguages());
          }
          elseif (!isset($delete_api_body_params[$id])) {
            $delete_translations[$id][] = $api_body_param;
          }
        }
      }

      if ($delete_api_body_params) {
        $this->storage->delete($delete_api_body_params);
        $this->logger('api_body_param')->notice('Deleted @count API HTTP Method Body Parameters.', ['@count' => count($delete_api_body_params)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $api_body_param = $api_body_params[$id]->getUntranslated();
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          foreach ($translations as $translation) {
            $api_body_param->removeTranslation($translation->language()->getId());
          }
          $api_body_param->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('api_body_param')->notice('Deleted @count API HTTP Method Body Parameter translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        $this->messenger()->addMessage($this->formatPlural($total_count, 'Deleted 1 API HTTP Method Body Parameter.', 'Deleted @count API HTTP Method Body Parameters.'));
      }

      $this->tempStoreFactory->get('api_body_param_multiple_delete_confirm')->delete($this->currentUser->id());
    }

    $form_state->setRedirect('entity.api_body_param.collection');
  }

}
