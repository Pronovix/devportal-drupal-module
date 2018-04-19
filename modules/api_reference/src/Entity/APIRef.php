<?php

namespace Drupal\devportal_api_reference\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\devportal_api_reference\APIRefInterface;
use Drupal\devportal_api_reference\Plugin\MigrationConfigDeriver;
use Drupal\devportal\Traits\URLRouteParametersTrait;
use Drupal\file\Entity\File;
use Drupal\user\UserInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Defines the API Reference entity class.
 *
 * @ContentEntityType(
 *   id = "api_ref",
 *   label = @Translation("API Reference"),
 *   handlers = {
 *     "storage" = "Drupal\devportal_api_reference\APIRefStorage",
 *     "list_builder" = "Drupal\devportal_api_reference\APIRefListBuilder",
 *     "view_builder" = "Drupal\devportal\DevportalContentEntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_reference\Form\APIRefForm",
 *       "add" = "Drupal\devportal_api_reference\Form\APIRefForm",
 *       "edit" = "Drupal\devportal_api_reference\Form\APIRefForm",
 *       "delete" = "Drupal\devportal\Form\DevportalContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\devportal_api_reference\APIRefHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\devportal_api_reference\APIRefAccessControlHandler",
 *     "translation" = "Drupal\devportal_api_reference\APIRefTranslationHandler",
 *   },
 *   admin_permission = "administer api refs",
 *   fieldable = TRUE,
 *   base_table = "api_ref",
 *   data_table = "api_ref_field_data",
 *   field_ui_base_route = "entity.api_ref_type.edit_form",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "revision" = "revision_id",
 *     "bundle" = "type",
 *     "langcode" = "langcode",
 *   },
 *   bundle_entity_type = "api_ref_type",
 *   bundle_label = @Translation("API Reference type"),
 *   revision_table = "api_ref_revision",
 *   revision_data_table = "api_ref_field_revision",
 *   show_revision_ui = TRUE,
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/api-reference/{api_ref}",
 *     "add-page" = "/api-reference/add",
 *     "add-form" = "/api-reference/add/{api_ref_type}",
 *     "edit-form" = "/api-reference/{api_ref}/edit",
 *     "delete-form" = "/api-reference/{api_ref}/delete",
 *     "collection" = "/admin/devportal/api-reference",
 *     "version-history" = "/api-reference/{api_ref}/revisions",
 *     "revision" = "/api-reference/{api_ref}/revisions/{api_ref_revision}/view",
 *     "revision_revert" = "/api-reference/{api_ref}/revisions/{api_ref_revision}/revert",
 *     "revision_delete" = "/api-reference/{api_ref}/revisions/{api_ref_revision}/delete",
 *     "multiple_delete_confirm" = "/admin/devportal/api-reference/delete",
 *     "translation_revert" = "/api-reference/{api_ref}/revisions/{api_ref_revision}/revert/{langcode}",
 *   },
 *   translatable = TRUE,
 * )
 */
class APIRef extends RevisionableContentEntityBase implements APIRefInterface {

  use EntityChangedTrait;
  use URLRouteParametersTrait;

  /**
   * Creates a shortened unique identifier.
   *
   * At the moment, it is a crc32b hash of the UUID. This is needed, because
   * the UUID is long, and along with the other parts of the table name, it will
   * exceed 63/64 characters, which are common limits to table name lengths.
   *
   * @return string
   */
  public function migrationID() {
    return hash('crc32b', $this->uuid());
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('uid');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * Alias for static::bundle().
   *
   * @return string
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * Returns all file IDs that are referenced by any revision of this entity.
   *
   * @return string[]
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAllSources() {
    $definition = \Drupal::entityTypeManager()->getDefinition($this->entityTypeId);
    $q = \Drupal::database()
      ->select($definition->getRevisionDataTable(), 'rt')
      ->fields('rt', ['source__target_id']);
    $q->condition('id', $this->id());

    return array_filter($q->execute()->fetchCol());
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function validate() {
    $validation = parent::validate();

    $version = NULL;
    try {
      $version = MigrationConfigDeriver::getVersionFromAPIRef($this);
      $file_ids = $this->getAllSources();
    }
    catch (\Exception $ex) {
      $msg = 'Invalid file.';
      $validation->add(new ConstraintViolation(
        t($msg),
        $msg,
        [],
        $this,
        'source',
        NULL
      ));
    }

    if (!empty($file_ids)) {
      /** @var \Drupal\Core\File\FileSystemInterface $file_system */
      $file_system = \Drupal::service('file_system');
      /** @var \Drupal\file\Entity\File[] $files */
      $files = \Drupal::entityTypeManager()->getStorage('file')->loadMultiple($file_ids);
      foreach ($files as $file) {
        $uri = $file->getFileUri();
        $path = $file_system->realpath($uri);
        try {
          $old_version = MigrationConfigDeriver::getVersion($this->getType(), $path);
          if ($old_version === $version) {
            $msg = 'This version (%version) is already in use.';
            $params = ['%version' => $version];
            $validation->add(new ConstraintViolation(
              t($msg, $params),
              $msg,
              $params,
              $this,
              'source',
              $old_version
            ));
          }
        }
        catch (\Exception $ex) {
          watchdog_exception('devportal_api_reference', $ex);
          $validation->add(new ConstraintViolation(
            $ex->getMessage(),
            $ex->getMessage(),
            [],
            $this,
            'source',
            $version
          ));
        }
      }
    }

    return $validation;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setSettings([
        'default' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Version'))
      ->setSettings([
        'default' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['source'] = BaseFieldDefinition::create('file')
      ->setLabel(t('Source file'))
      ->setDisplayOptions('view', [
        'type' => 'file_default',
        'label' => 'above',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'file_generic',
        'weight' => 0,
      ])
      ->setSettings([
        'file_extensions' => 'yml yaml json',
      ])
      ->setCardinality(1)
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The username of the API Documentation author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      // This is fine since we depend on the node module.
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['project_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Project ID'))
      ->setDescription(t('Internal ID of the project'))
      ->setSettings([
        'default' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'region' => 'hidden',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'region' => 'hidden',
        'weight' => 3,
      ])
      ->setCardinality(1)
      ->setReadOnly(TRUE)
      ->setRequired(FALSE)
      ->setRevisionable(FALSE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilterPattern() {
    $migration_id = $this->migrationID();
    return "/^devportal_api_reference:{$migration_id}_/";
  }

  /**
   * {@inheritdoc}
   */
  public function cleanUp($all = FALSE) {
    // TODO: Implement cleanUp() method if needed.
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\devportal_api_reference\Plugin\Swagger20ValidationException
   */
  public function getMigrationAfterCallbacks() {
    $id = $this->id();
    $version = (string) MigrationConfigDeriver::getVersionFromAPIRef($this);
    $callbacks = [
      [
        [static::class, 'updateEntityAfterMigration'],
        [$id, $version],
      ],
    ];
    $hook_callbacks = \Drupal::moduleHandler()->invokeAll('api_ref_migration_after_callbacks', [$id, $version]);
    return array_merge_recursive($callbacks, $hook_callbacks ?: []);
  }

  /**
   * Updates an API Reference entity after migration.
   *
   * This function runs after migration and performs updates on a API Reference
   * entity.
   *
   * @param int $api_ref_id
   *   The current API Reference id.
   * @param string $api_version
   *   Imported API version.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function updateEntityAfterMigration(int $api_ref_id, string $api_version) {
    /** @var \Drupal\devportal_api_reference\Entity\APIRef $api_ref */
    $api_ref = APIRef::load($api_ref_id);

    // Update API Reference's Title, Description and Version fields to be in
    // line with the just imported API Documentation.
    $source = $api_ref->get('source')->getValue();
    if (!empty($source[0]['target_id'])) {
      /** @var \Drupal\file\Entity\File $file */
      $file = File::load($source[0]['target_id']);
      try {
        $swagger = MigrationConfigDeriver::parseSwagger($file->getFileUri());
        $api_ref->set('title', $swagger['info']['title']);
        $api_ref->set('version', $swagger['info']['version']);
        if (!empty($swagger['info']['description'])) {
          $api_ref->set('description', [
            'value' => $swagger['info']['description'],
            'format' => 'github_flavored_markdown',
          ]);
        }
      }
      catch (\Exception $e) {
        // Do nothing.
      }
    }

    $api_ref->save();
  }

  /**
   * {@inheritdoc}
   */
  public function relatedGenerators() {
    return [];
  }

}
