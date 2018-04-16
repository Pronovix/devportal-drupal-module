<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the APIDoc entity.
 *
 * @see \Drupal\devportal_api_entities\Entity\APIDoc.
 */
class APIDocAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer api docs')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    /** @var \Drupal\devportal_api_entities\APIDocInterface $entity */
    $is_owner = ($account->id() && $account->id() === $entity->getOwnerId());
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIf($account->hasPermission('view api docs') && ($is_owner || $entity->isPublished()))
          ->cachePerPermissions()
          ->addCacheableDependency($entity);

      case 'update':
        return AccessResult::allowedIf($account->hasPermission('edit api docs') && $is_owner)
          ->cachePerPermissions()
          ->cachePerUser()
          ->addCacheableDependency($entity);

      case 'delete':
        return AccessResult::allowedIf($account->hasPermission('delete api docs') && $is_owner)
          ->cachePerPermissions()
          ->cachePerUser()
          ->addCacheableDependency($entity);

      default:
        // Unknown operation, no opinion.
        return AccessResult::neutral()->cachePerPermissions();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add api docs');
  }

}

