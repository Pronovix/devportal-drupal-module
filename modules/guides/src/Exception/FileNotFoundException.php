<?php

namespace Drupal\guides\Exception;

use Throwable;

/**
 * Copyright (C) 2019 PRONOVIX GROUP BVBA.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,
 * USA.
 */

/**
 * Module specific FileNotFound exception.
 */
class FileNotFoundException extends RuntimeException {

  /**
   * The path of the file.
   *
   * @var string
   */
  protected $path;

  /**
   * FileNotFoundException constructor.
   *
   * @param string $path
   *   The path of the file.
   * @param \Throwable|null $previous
   *   Previous exception.
   */
  public function __construct(string $path, \Throwable $previous = NULL) {
    $this->path = $path;
    parent::__construct('File not found:' . $path, 0, $previous);
  }

  /**
   * The path of the file.
   *
   * @return string
   *   The path of the file.
   */
  public function getPath() {
    return $this->path;
  }

}
