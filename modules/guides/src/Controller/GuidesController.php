<?php

namespace Drupal\guides\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Site\Settings;
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

  /**
   * List guides.
   *
   * @return array
   *   Render array.
   */
  public function listGuides() {
    $guides = [];
    $guides_dir = DRUPAL_ROOT . (Settings::get('guides_dir') ?? '/guides');

    foreach (array_diff(scandir($guides_dir), ['..', '.']) as $guide_dir) {
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
        '#items' => $guides,
      ];
    }
    else {
      return [
        '#markup' => $this->t('<strong>@text</strong>', ['@text' => 'No guides found.']),
      ];
    }
  }

  /**
   * Page callback that renders a markdown file on the UI.
   *
   * @return array
   *   Render array.
   */
  public function guideContent($filename) {
    $guides_dir = Settings::get('guides_dir') ?? '/guides';
    $target = [
      'dir' => FALSE,
      'file' => FALSE,
    ];

    foreach (array_diff(scandir(DRUPAL_ROOT . $guides_dir), ['..', '.']) as $guide_dir) {
      $dir = DRUPAL_ROOT . $guides_dir . '/' . $guide_dir;
      $file = $dir . '/' . $filename . '.md';
      if (file_exists($file)) {
        $target['dir'] = $guides_dir . '/' . $guide_dir;
        $target['file'] = $file;
        break;
      }
    }

    if (!$target['file']) {
      throw new NotFoundHttpException();
    }

    $md = new Parsedown();
    $md = $md->text(str_replace('@guide_path', $target['dir'], file_get_contents($target['file'])));

    return [
      '#markup' => $md,
      '#attached' => [
        'library' => [
          'guides/guide',
          'guides/in_page_navigation',
        ],
      ],
    ];
  }

}
