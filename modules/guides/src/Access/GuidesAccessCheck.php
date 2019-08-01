<?php

namespace Drupal\guides\Access;

/**
 * Copyright (C) 2019 PRONOVIX GROUP BVBA.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,
 *  USA.
 */

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\guides\GuidesStorageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GuidesAccessCheck.
 */
class GuidesAccessCheck implements AccessInterface {

  /**
   * The guides storage.
   *
   * @var \Drupal\guides\GuidesStorageInterface
   */
  protected $guidesStorage;

  /**
   * GuidesAccessCheck constructor.
   *
   * @param \Drupal\guides\GuidesStorageInterface $guides_storage
   *   The guides storage.
   */
  public function __construct(GuidesStorageInterface $guides_storage) {
    $this->guidesStorage = $guides_storage;
  }

  /**
   * Grant access to the guides based on the existence of the files.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match interface.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(RouteMatchInterface $route_match, AccountInterface $account): AccessResultInterface {
    if ($account->hasPermission('access guides')) {
      $file = $this->guidesStorage->getFile($route_match->getParameter('subdirectory'), $route_match->getParameter('filename'));

      if ($file) {
        return AccessResult::allowed();
      }
      // Return 404 instead of 403.
      elseif (!$file) {
        throw new NotFoundHttpException();
      }

    }
    else {
      return AccessResult::forbidden();
    }
  }

}
