<?php

/**
 * @file
 * Page callbacks for API Info entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Info templates.
 *
 * Default template: api-info.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Info information
 *     and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_info(array &$variables) {
  // Fetch API Info Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIInfo $entity */
  $entity = $variables['elements']['#api_info'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
