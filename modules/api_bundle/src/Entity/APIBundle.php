<?php

namespace Drupal\devportal_api_bundle\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\devportal_api_bundle\APIBundleInterface;
use Drupal\devportal_api_bundle\Traits\URLRouteParametersTrait;

/**
 * Defines the API Bundle entity class.
 *
 * @ContentEntityType(
 *   id = "api_bundle",
 *   label = @Translation("API Bundle"),
 *   handlers = {
 *     "storage" = "Drupal\devportal_api_bundle\APIBundleStorage",
 *     "list_builder" = "Drupal\devportal_api_bundle\APIBundleListBuilder",
 *     "view_builder" = "Drupal\devportal_api_bundle\DHLAPIEntitiesContentEntityViewBuilder",
 *     "views_data" = "Drupal\devportal_api_bundle\APIBundleViewsData",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_bundle\Form\DHLAPIEntitiesContentEntityForm",
 *       "add" = "Drupal\devportal_api_bundle\Form\DHLAPIEntitiesContentEntityForm",
 *       "edit" = "Drupal\devportal_api_bundle\Form\DHLAPIEntitiesContentEntityForm",
 *       "delete" = "Drupal\devportal_api_bundle\Form\DHLAPIEntitiesContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\devportal_api_bundle\APIBundleHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\devportal_api_bundle\APIBundleAccessControlHandler",
 *     "translation" = "Drupal\devportal_api_bundle\APIBundleTranslationHandler",
 *   },
 *   admin_permission = "administer api bundles",
 *   fieldable = TRUE,
 *   base_table = "api_bundle",
 *   data_table = "api_bundle_field_data",
 *   field_ui_base_route = "entity.api_bundle_type.edit_form",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "revision" = "vid",
 *     "langcode" = "langcode",
 *     "label" = "title",
 *   },
 *   bundle_entity_type = "api_bundle_type",
 *   bundle_label = @Translation("API Bundle type"),
 *   revision_table = "api_bundle_revision",
 *   revision_data_table = "api_bundle_field_revision",
 *   show_revision_ui = TRUE,
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/api-bundle/{api_bundle}",
 *     "add-page" = "/api-bundle/add",
 *     "add-form" = "/api-bundle/add/{api_bundle_type}",
 *     "edit-form" = "/api-bundle/{api_bundle}/edit",
 *     "delete-form" = "/api-bundle/{api_bundle}/delete",
 *     "collection" = "/admin/devportal/api-bundle",
 *     "version-history" = "/api-bundle/{api_bundle}/revisions",
 *     "revision" = "/api-bundle/{api_bundle}/revisions/{api_bundle_revision}/view",
 *     "revision_revert" = "/api-bundle/{api_bundle}/revisions/{api_bundle_revision}/revert",
 *     "revision_delete" = "/api-bundle/{api_bundle}/revisions/{api_bundle_revision}/delete",
 *     "multiple_delete_confirm" = "/admin/devportal/api-bundle/delete",
 *     "translation_revert" = "/api-bundle/{api_bundle}/revisions/{api_bundle_revision}/revert/{langcode}",
 *   },
 *   translatable = TRUE,
 * )
 */
class APIBundle extends RevisionableContentEntityBase implements APIBundleInterface {

  use EntityChangedTrait;
  use URLRouteParametersTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getImage() {
    return $this->get('image')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setImage($image) {
    $this->set('image', $image);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAPIs() {
    // TODO: !!!
    return $this->get('api')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAPIs($apis) {
    $this->set('api', $apis);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addAPI($api) {
    $this->set('api', $api);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthor() {
    return $this->get('author')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthor($author) {
    $this->set('author', $author);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing APIBundle without adding a new
      // revision, we need to make sure $entity->revision_log is reset whenever
      // it is empty. Therefore, this code allows us to avoid clobbering an
      // existing log entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id']->setDescription(t('The API Bundle ID.'));

    $fields['uuid']->setDescription(t('The API Bundle UUID.'));

    $fields['vid']->setDescription(t('The API Bundle revision ID.'));

    $fields['langcode']->setDescription(t('The API Bundle language code.'));

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textfield',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['api_ref'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('API Reference'))
      ->setDescription(t('API References referenced from API Bundle.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRevisionable(TRUE)
      ->setSettings([
        'target_type' => 'api_ref',
        'handler' => 'default',
        'handler_settings' => [
          'sort' => [
            'field' => '_none',
          ],
          'auto_create' => FALSE,
          'auto_create_bundle' => '',
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 15,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the API Bundle was last edited.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    return $fields;
  }

}
