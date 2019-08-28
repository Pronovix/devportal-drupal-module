<?php

namespace Drupal\guides;

use Drupal\guides\Exception\FileNotFoundException;

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
 * Defines an interface for guides storage classes.
 */
interface GuidesStorageInterface {

  /**
   * Gets the path of the guides files which are in the guides subdirectories.
   *
   * The returned list of files may contain paths of files that does not
   * actually exist in the filesystem.
   *
   * @return array
   *   The array of the paths of the guides files.
   */
  public function getFilePaths(): array;

  /**
   * Get the path of the given file name in the given subdirectory.
   *
   * @param string $path
   *   The relative path of the guides file without extension.
   *
   * @return string
   *   The path of the given file name in the given subdirectory.
   *
   * @throws \Drupal\guides\Exception\FileNotFoundException
   *   Thrown if the file with the given name and subdirectory is not found.
   */
  public function getFilePath(string $path): string;

  /**
   * Gets the content of the given file.
   *
   * Parses a markdown file or throws an exception if the file doesn't exist or
   * can't be parsed.
   *
   * @param string $path
   *   The relative path of the guides file without extension.
   *
   * @return string
   *   The content of the file or an exception.
   *
   * @throws \Drupal\guides\Exception\FileNotFoundException
   *   Thrown if the file with the given name and subdirectory is not found.
   */
  public function getFileContent(string $path): string;

  /**
   * Gets the links of the guides files.
   *
   * This method intentionally does not validate whether a file still exists or
   * not, but the getFilePath() method validates it and because it is called by the
   * route access handler, therefore the
   * http://example.com/guides/file_does_not_exists_anymore page will return
   * a 404.
   *
   * @return array
   *   The array of the links of the files.
   */
  public function getLinks(): array;

}
