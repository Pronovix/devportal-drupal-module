<?php

/**
 * @file
 * Page callbacks for API External Documentation entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for API External Documentation templates.
 *
 * Default template: api-ext-doc.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API External
 *     Documentation information and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_ext_doc(array &$variables) {
  // Fetch API External Documentation Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIExtDoc $entity */
  $entity = $variables['elements']['#api_ext_doc'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

