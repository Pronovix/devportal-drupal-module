<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for API Version Tags.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class APIVersionTagHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    if ($collection_route = $this->getCollectionRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.collection", $collection_route);
    }

    if ($multiple_delete_route = $this->getMultipleDeleteRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.multiple_delete_confirm", $multiple_delete_route);
    }

    return $collection;
  }

  /**
   * Gets the add page route.
   *
   * Built only for entity types that have bundles.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getAddPageRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('add-page') && $entity_type->getKey('bundle')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('add-page'));
      $route->setDefaults([
          '_controller' => '\Drupal\devportal_api_entities\Controller\APIVersionTagController::addPage',
          '_title' => "Add {$entity_type->getLabel()}",
          'entity_type_id' => $entity_type_id,
        ])
        ->setRequirement('_entity_create_any_access', $entity_type->id())
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('collection') && $entity_type->hasListBuilderClass()) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('collection'));
      $route
        ->setDefaults([
          '_entity_list' => $entity_type_id,
          '_title' => "{$entity_type->getLabel()} list",
        ])
        ->setRequirement('_permission', 'access api version tag overview')
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

  /**
   * Gets the multiple delete route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getMultipleDeleteRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('multiple_delete_confirm')) {
      $route = new Route($entity_type->getLinkTemplate('multiple_delete_confirm'));
      $route
        ->setDefaults([
          '_form' => '\Drupal\devportal_api_entities\Form\DeleteMultipleAPIVersionTags',
          '_title' => 'Delete multiple API Version Tags',
        ])
        ->setRequirement('_permission', 'delete api version tags')
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

}
