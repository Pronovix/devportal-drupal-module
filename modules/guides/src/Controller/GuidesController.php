<?php

namespace Drupal\guides\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Parsedown;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * User guide page.
 */
class GuidesController extends ControllerBase {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Creates a new HelpController.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\help\HelpSectionManager $help_manager
   *   The help section manager.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match')
    );
  }

  public function listGuides() {
    $guides = [];
    $guides_dir = drupal_get_path('module', 'guides') . '/guides';

    foreach (array_diff(scandir($guides_dir), array('..', '.')) as $guide_dir) {
      $dir = $guides_dir . '/' . $guide_dir;
      if (is_dir($dir)) {
        foreach (glob($dir . '/*.md') as $md) {
          if (file_exists($md)) {
            $parts = pathinfo($md);
            $link = Url::fromRoute('guides.guide', ['filename' => $parts['filename']]);
            $link = Link::fromTextAndUrl(str_replace('_', ' ', $guide_dir), $link)->toString();
            $guides[] = [
              '#markup' => $link,
            ];
          }
        }
      }
    }

    if (!empty($guides)) {
      return [
        '#theme' => 'item_list',
        '#items' => $guides
      ];
    }
    else {
      return [
        '#markup' => $this->t('<strong>@text</strong>', ['@text' => 'No guides found.']),
      ];
    }
  }

  public function guideContent($filename) {
    $guides_dir = drupal_get_path('module', 'guides') . '/guides';
    $target = [
      'dir' => FALSE,
      'file' => FALSE,
    ];

    foreach (array_diff(scandir($guides_dir), array('..', '.')) as $guide_dir) {
      $dir = $guides_dir . '/' . $guide_dir;
      $file = $dir . '/' . $filename . '.md';
      if (file_exists($file)) {
        $target['dir'] = $dir;
        $target['file'] = $file;
        break;
      }
    }

    if (!$target['file']) {
      throw new NotFoundHttpException();
    }

    $md = new Parsedown();
    $md = $md->text(str_replace('@guide_path', base_path() . $target['dir'], file_get_contents($target['file'])));

    return [
      '#markup' => $md,
      '#attached' => [
        'library' => [
          'guides/guide'
        ]
      ]
    ];
  }
}