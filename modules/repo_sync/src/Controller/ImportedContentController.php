<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Extra endpoints / pages for the "imported content" content type.
 */
class ImportedContentController extends ControllerBase implements AccessInterface {

  /**
   * Page handler for entity.node.imported_content.redirectToEdit.
   *
   * @param Node $node
   *
   * @return RedirectResponse
   */
  public function redirectToEdit(Node $node) {
    /** @var MigrateSourcePluginManager $source_plugin_manager */
    $source_plugin_manager = \Drupal::service('plugin.manager.migrate.source');

    $provider = $node->get('field_ic_provider')->getString();
    $repository = $node->get('field_ic_repository')->getString();
    $version = $node->get('field_ic_version')->getString();
    $path = $node->get('field_ic_filename')->getString();

    $definition = $source_plugin_manager->getDefinition($provider);
    $pattern = $definition['editLinkPattern'];

    $redirect = strtr($pattern, [
      '{repository}' => $repository,
      '{version}' => $version,
      '{path}' => $path,
    ]);

    return new TrustedRedirectResponse($redirect, 301);
  }

  /**
   * Access callback for various handlers this controller provide.
   *
   * Only allows access if the user has edit permission for the node and if the
   * node is an 'imported_content'.
   *
   * @param AccountInterface $account
   * @param RouteMatch $routeMatch
   *
   * @return AccessResultInterface
   */
  public function access(AccountInterface $account, RouteMatch $routeMatch) {
    /** @var Node $node */
    $node = $routeMatch->getParameter('node');

    return $node->getType() === 'imported_content' && $node->access('update', $account) ?
      AccessResult::allowed() : AccessResult::forbidden();
  }

}
