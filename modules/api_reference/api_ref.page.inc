<?php

/**
 * @file
 * Page callbacks for API Reference entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Reference templates.
 *
 * Default template: api-ref.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Reference
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_ref(array &$variables) {
  // Fetch API Reference Entity Object.
  /** @var \Drupal\devportal_api_reference\Entity\APIRef $entity */
  $entity = $variables['elements']['#api_ref'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
