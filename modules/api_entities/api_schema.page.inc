<?php

/**
 * @file
 * Page callbacks for API Schema entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Schema templates.
 *
 * Default template: api-schema.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Schema information
 *     and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_schema(array &$variables) {
  // Fetch API Schema Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APISchema $entity */
  $entity = $variables['elements']['#api_schema'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
