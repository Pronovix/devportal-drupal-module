<?php

/**
 * @file
 * Page callbacks for API Version Tag entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Version Tag templates.
 *
 * Default template: api-version-tag.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Version Tag information
 *     and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_version_tag(array &$variables) {
  // Fetch API Version Tag Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIVersionTag $entity */
  $entity = $variables['elements']['#api_version_tag'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
