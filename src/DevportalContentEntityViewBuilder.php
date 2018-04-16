<?php

namespace Drupal\devportal;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * View builder handler for Devportal Docs content entities.
 */
class DevportalContentEntityViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    parent::alterBuild($build, $entity, $display, $view_mode);
    // Add contextual links.
    $entity_type_id = $entity->getEntityType()->id();
    $build['#contextual_links'][$entity_type_id] = [
      'route_parameters' => [$entity_type_id => $entity->id()],
      'metadata' => ['changed' => $entity->getChangedTime()],
    ];
  }

}
