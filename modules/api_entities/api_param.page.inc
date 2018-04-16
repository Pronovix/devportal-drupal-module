<?php

/**
 * @file
 * Page callbacks for API Parameter entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Parameter templates.
 *
 * Default template: api-param.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Parameter information
 *     and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_param(array &$variables) {
  // Fetch API Parameter Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIParam $entity */
  $entity = $variables['elements']['#api_param'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
