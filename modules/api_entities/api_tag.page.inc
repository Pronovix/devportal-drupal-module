<?php

/**
 * @file
 * Page callbacks for API Tag entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Tag templates.
 *
 * Default template: api-tag.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Tag information and
 *     any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_tag(array &$variables) {
  // Fetch API Tag Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APITag $entity */
  $entity = $variables['elements']['#api_tag'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
