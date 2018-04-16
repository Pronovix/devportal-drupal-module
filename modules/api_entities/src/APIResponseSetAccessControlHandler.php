<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the APIResponseSet entity.
 *
 * @see \Drupal\devportal_api_entities\Entity\APIResponseSet.
 */
class APIResponseSetAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\devportal_api_entities\APIResponseSetInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view api response sets');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit api response sets');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete api response sets');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add api response sets');
  }

}

