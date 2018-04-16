<?php

/**
 * @file
 * Page callbacks for API Method entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;

/**
 * Prepares variables for API Method templates.
 *
 * Default template: api-method.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the API Method information
 *     and any fields attached to it.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_api_method(array &$variables) {
  // Fetch API Method Entity Object.
  /** @var \Drupal\devportal_api_entities\Entity\APIMethod $entity */
  $entity = $variables['elements']['#api_method'];

  // Helpful classes for the wrapper.
  $variables['attributes']['class'][] = $entity->getEntityTypeId() . '--' . $entity->bundle();
  $variables['attributes']['class'][] = $entity->bundle();
  $variables['attributes']['class'][] = $entity->getEntityTypeId();

  // Id to be used for in-site navigation.
  $id_elements = [
    $entity->getEntityTypeId(),
    $variables['elements']['#view_mode'],
    $entity->id(),
  ];
  $variables['attributes']['id'] = Html::getClass(implode('--', $id_elements));

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
