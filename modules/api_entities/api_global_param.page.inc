<?php

/**
 * @file
 * Page callbacks for API Global Parameter entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Global Parameter templates.
 *
 * Default template: api-global-param.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Global Parameter
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_global_param(array &$variables) {
  // Fetch API Global Parameter Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIGlobalParam $entity */
  $entity = $variables['elements']['#api_global_param'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
