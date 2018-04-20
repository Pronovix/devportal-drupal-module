<?php

namespace Drupal\devportal\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;


/**
 * Sets the _admin_route for specific API entity related routes.
 */
class DevportalAdminEventSubscriber extends RouteSubscriberBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new DevportalAdminEventSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($this->configFactory->get('node.settings')->get('use_admin_theme')) {
      /** @var \Symfony\Component\Routing\Route $route */
      foreach ($collection->all() as $name => $route) {
        if (preg_match('/^entity\.api_[a-z0-9_]+\.(add|edit)_form$/i', $name)) {
          $route->setOption('_admin_route', TRUE);
        }
      }
    }
  }

}
