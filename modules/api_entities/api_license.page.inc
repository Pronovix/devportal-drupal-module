<?php

/**
 * @file
 * Page callbacks for API License entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API License templates.
 *
 * Default template: api-license.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API License information
 *     and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_license(array &$variables) {
  // Fetch API License Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APILicense $entity */
  $entity = $variables['elements']['#api_license'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
