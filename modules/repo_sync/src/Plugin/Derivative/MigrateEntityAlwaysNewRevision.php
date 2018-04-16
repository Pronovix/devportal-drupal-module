<?php

namespace Drupal\devportal_repo_sync\Plugin\Derivative;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\migrate\Plugin\Derivative\MigrateEntity;
use Drupal\devportal_repo_sync\Plugin\migrate\destination\EntityAlwaysNewRevisionDestination;

class MigrateEntityAlwaysNewRevision extends MigrateEntity {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $prefix = EntityAlwaysNewRevisionDestination::PREFIX;
    foreach ($this->entityDefinitions as $entity_type => $entity_info) {
      if (!is_subclass_of($entity_info->getClass(), ContentEntityInterface::class)) {
        continue;
      }

      $this->derivatives[$entity_type] = [
        'id' => "{$prefix}:{$entity_type}",
        'class' => EntityAlwaysNewRevisionDestination::class,
        'requirements_met' => 1,
        'provider' => $entity_info->getProvider(),
      ];
    }

    return $this->derivatives;
  }

}
