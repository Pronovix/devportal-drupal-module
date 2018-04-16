<?php

/**
 * @file
 * Page callbacks for API Header Parameter entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Header Parameter templates.
 *
 * Default template: api-header-param.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Header Parameter
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_header_param(array &$variables) {
  // Fetch API Header Parameter Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIHeaderParam $entity */
  $entity = $variables['elements']['#api_header_param'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
