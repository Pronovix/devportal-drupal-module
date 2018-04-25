<?php

/**
 * @file
 * Page callbacks for API Bundle entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Bundle templates.
 *
 * Default template: api-bundle.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Bundle
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_bundle(array &$variables) {
  // Fetch API Bundle Entity Object.
  /** @var \Drupal\devportal_api_bundle\Entity\APIBundle $entity */
  $entity = $variables['elements']['#api_bundle'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();
  $variables['attributes']['class'][] = "view-mode--{$variables['elements']['#view_mode']}";

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
