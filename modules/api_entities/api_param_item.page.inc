<?php

/**
 * @file
 * Page callbacks for API Parameter Item entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Parameter Item templates.
 *
 * Default template: api-param-item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Parameter Item
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_param_item(array &$variables) {
  // Fetch API Parameter Item Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIParamItem $entity */
  $entity = $variables['elements']['#api_param_item'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
