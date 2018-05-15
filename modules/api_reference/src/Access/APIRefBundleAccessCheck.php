<?php

namespace Drupal\devportal_api_reference\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Determines access based on API Reference related node bundles.
 */
class APIRefBundleAccessCheck implements AccessInterface {

  /**
   * Checks access based on API Reference related node bundles.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, NodeInterface $node = NULL) {
    if (in_array($node->bundle(), devportal_api_reference_bundles(), TRUE)) {
      return AccessResult::allowed();
    }
    // No opinion.
    return AccessResult::neutral();
  }

}
