<?php

/**
 * @file
 * Page callbacks for API Meta Parameter entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API Meta Parameter templates.
 *
 * Default template: api-meta-param.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Meta Parameter
 *     information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_meta_param(array &$variables) {
  // Fetch API Meta Parameter Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIMetaParam $entity */
  $entity = $variables['elements']['#api_meta_param'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
