<?php

namespace Drupal\devportal\Traits;

/**
 * Provides default route parameters for some entity route.
 */
trait URLRouteParametersTrait {

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    // For some reason the menu_link_content_entity_predelete() function
    // indirectly generates entity URLs based on the entity type's "links"
    // annotation property. Because of this all URL parameters have to be
    // provided for each URL path. It seems core doesn't assume that the "links"
    // array can contain the "revision_revert", "revision_delete" and the
    // "translation_revert" paths (like in our case) because it doesn't provide
    // values for all URL parameters they use. This causes fatal PHP errors in
    // case of an entity delete operation. Due to the above mentioned things we
    // provide the missing URL parameters here.
    if (in_array($rel, ['revision_revert', 'revision_delete', 'translation_revert'], TRUE)
      && !isset($uri_route_parameters[$this->getEntityTypeId() . '_revision'])) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    if (($rel === 'translation_revert') && !isset($uri_route_parameters['langcode'])) {
      $uri_route_parameters['langcode'] = $this->language()->getId();
    }
    return $uri_route_parameters;
  }

}
