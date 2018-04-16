<?php

/**
 * @file
 * Page callbacks for API Query Parameter entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Query Parameter templates.
 *
 * Default template: api-query-param.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Query Parameter
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_query_param(array &$variables) {
  // Fetch API Query Parameter Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIQueryParam $entity */
  $entity = $variables['elements']['#api_query_param'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
