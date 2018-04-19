<?php

namespace Drupal\devportal_api_bundle;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the APIBundle entity.
 *
 * @see \Drupal\devportal_api_bundle\Entity\APIBundle.
 */
class APIBundleAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\devportal_api_bundle\APIBundleInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view api bundles');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit api bundles');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete api bundles');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add api bundles');
  }

}
