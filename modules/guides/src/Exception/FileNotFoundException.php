<?php

namespace Drupal\guides\Exception;

use Drupal\guides\GuidesInterface;

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

  public function __construct(string $path, string $message = 'File not found: @path.', int $code = 0, \Throwable $previous = null) {
    $message = strtr($message, ['@path' => $path]);
    $this->path = $path;
    parent::__construct($message, $code, $previous);
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
