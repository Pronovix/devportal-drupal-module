<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\destination;

use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\migrate\id_map\Sql;
use Drupal\migrate\Row;

/**
 * Provides a destination plugin that always tries to create a new revision.
 *
 * @MigrateDestination(
 *   id = "entity_newrevision",
 *   deriver = "Drupal\devportal_repo_sync\Plugin\Derivative\MigrateEntityAlwaysNewRevision",
 * )
 */
class EntityAlwaysNewRevisionDestination extends EntityContentBase {

  const PREFIX = 'entity_newrevision';

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    return substr($plugin_id, strlen(static::PREFIX) + 1);
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateSkipRowException
   */
  protected function getEntity(Row $row, array $old_destination_id_values) {
    /** @var \Drupal\Core\Entity\ContentEntityBase $entity */
    $entity = parent::getEntity($row, $old_destination_id_values);

    /** @var \Drupal\devportal_repo_sync\Plugin\migrate\source\RepositorySource $sourcePlugin */
    $sourcePlugin = $this->migration->getSourcePlugin();

    /** @var \Drupal\migrate\Plugin\migrate\id_map\Sql $idmap */
    $idmap = $this->migration->getIdMap();

    $last_imported = (int) \Drupal::database()->select($idmap->getQualifiedMapTableName(), 'm')
      ->fields('m', ['last_imported'])
      ->condition(Sql::SOURCE_IDS_HASH, $idmap->getSourceIDsHash($row->getSourceIdValues()))
      ->execute()
      ->fetchField();

    if ($last_imported) {
      $filename = $row->getSourceProperty('filename');
      $files = $sourcePlugin->getChangedFilesSince(date_iso8601($last_imported));

      if (!isset($files[$filename])) {
        throw new MigrateSkipRowException('No updates since the last import');
      }
    }

    $entity->setNewRevision();
    if (method_exists($entity, 'setRevisionCreationTime')) {
      $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
    }
    if (!$this->configuration['publish_changes']) {
      $entity->isDefaultRevision(FALSE);
    }

    return $entity;
  }

}
