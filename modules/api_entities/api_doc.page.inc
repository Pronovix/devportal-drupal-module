<?php

/**
 * @file
 * Page callbacks for API Documentation entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Documentation templates.
 *
 * Default template: api-doc.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Documentation
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_doc(array &$variables) {
  // Fetch API Documentation Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIDoc $entity */
  $entity = $variables['elements']['#api_doc'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
