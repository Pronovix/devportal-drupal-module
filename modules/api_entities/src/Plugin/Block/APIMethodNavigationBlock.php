<?php

namespace Drupal\devportal_api_entities\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;

/**
 * Provides a 'API Method Navigation' Block.
 *
 * @Block(
 *   id = "api_method_navigation_block",
 *   admin_label = @Translation("API Method Navigation"),
 *   category = @Translation("API Method Navigation"),
 * )
 */
class APIMethodNavigationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $api_ref = \Drupal::routeMatch()->getParameter('api_ref');
    $api_ref_id = \Drupal::routeMatch()->getParameter('api_ref')->id();

    // If the migration has already happened.
    if (!$api_ref->get('api_doc_ref')->isEmpty()) {
      /** @var \Drupal\devportal_api_entities\Entity\APIDoc $api_doc */
      $api_doc = $api_ref->get('api_doc_ref')->referencedEntities()[0];
      $api_version_tag_id = $api_doc->get('api_version_tag')->getValue()[0]['target_id'];

      $api_method_ids = \Drupal::entityQuery('api_method')
        ->condition('api_ref', $api_ref_id)
        ->condition('api_version_tag', $api_version_tag_id)
        ->execute();

      /** @var \Drupal\devportal_api_entities\Entity\APIMethod[] $api_methods */
      $api_methods = \Drupal::entityTypeManager()
        ->getStorage('api_method')
        ->loadMultiple($api_method_ids);

      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('api_method');
      $methods = array_map(function ($api_method) use ($view_builder) {
        /** @var \Drupal\devportal_api_entities\Entity\APIMethod $api_method */
        return $view_builder->view($api_method, 'compact');
      }, $api_methods);

      // Group API methods by tag.
      $tags = [];

      foreach ($methods as &$method) {
        /** @var \Drupal\devportal_api_entities\Entity\APIMethod $method */
        $fragment_elements = [
          $method['#api_method']->getEntityTypeId(),
          'full',
          $method['#api_method']->id(),
        ];
        $options = [
          'fragment' => Html::getClass(Html::getClass(implode('--', $fragment_elements))),
        ];
        $url = Url::fromUri('internal:', $options);
        $method_as_link = Link::fromTextAndUrl($method, $url);

        if (!empty($method['#api_method']->get('api_tag')->referencedEntities())) {
          foreach ($method['#api_method']->get('api_tag')->referencedEntities() as $tag) {
            /** @var \Drupal\devportal_api_entities\Entity\APITag $tag */
            $tags[$tag->getName()][] = $method_as_link;
          }
        }
        else {
          $tags['default'][] = $method_as_link;
        }
      }

      ksort($tags);

      return [
        '#theme' => 'api_method_navigation_block',
        '#tags' => $tags,
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }

    return [
      '#markup' => '',
    ];
  }

}
