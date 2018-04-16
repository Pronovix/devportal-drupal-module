<?php

namespace Drupal\devportal_api_reference;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the APIRef entity.
 *
 * @see \Drupal\devportal_api_reference\Entity\APIRef.
 */
class APIRefAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\devportal_api_reference\APIRefInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view api refs');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit api refs');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete api refs');
    }

    // Unknown operation, no opinion.
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add api refs');
  }

}
