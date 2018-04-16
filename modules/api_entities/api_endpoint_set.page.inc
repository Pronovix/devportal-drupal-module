<?php

/**
 * @file
 * Page callbacks for API Endpoint Set entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Endpoint Set templates.
 *
 * Default template: api-endpoint-set.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Endpoint Set
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_endpoint_set(array &$variables) {
  // Fetch API Endpoint Set Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIEndpointSet $entity */
  $entity = $variables['elements']['#api_endpoint_set'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

