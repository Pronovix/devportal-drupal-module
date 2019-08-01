<?php

namespace Drupal\guides;

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

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\guides\Exception\FileNotFoundException;

/**
 * Defines GuidesStorage for Guides files, links and directories.
 */
final class GuidesStorage implements GuidesStorageInterface {

  /**
   * The settings of the site.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The cache backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * GuidesStorage constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings of the site.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   */
  public function __construct(Settings $settings, CacheBackendInterface $cache_backend) {
    $this->settings = $settings;
    $this->cacheBackend = $cache_backend;
  }

  /**
   * {@inheritdoc}
   */
  private function getDirectory(): string {
    return DRUPAL_ROOT . ($this->settings->get('guides_dir') ?? '/guides');
  }

  /**
   * {@inheritdoc}
   */
  public function getFiles(): array {
    $directory = $this->getDirectory();
    $cid = 'guides_files:' . $directory;
    $data_cached = $this->cacheBackend->get($cid);

    if ($data_cached) {
      $guides = $data_cached->data;
    }
    else {
      $iterator = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS);
      $filter = new \RecursiveCallbackFilterIterator($iterator, function ($current, $key, $iterator) {
        if ($iterator->hasChildren()) {
          return TRUE;
        }
        $path = $current->getPathname();
        if (is_file($path)) {
          return substr($current->getFilename(), -3) == '.md';
        }

        return FALSE;
      });

      $guides = [];
      $files = new \RecursiveIteratorIterator($filter);
      foreach ($files as $md) {
        if ($files->getDepth() == 1) {
          $guides[] = $md->getPathname();
        }
      }

      $this->cacheBackend->set($cid, $guides);
    }

    return $guides;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile(string $subdirectory, string $file_name): string {
    $file_path = $this->getDirectory() . '/' . $subdirectory . '/' . $file_name . '.md';

    if (file_exists($file_path) && in_array($file_path, $this->getFiles())) {
      return $file_path;
    }
    else {
      throw new FileNotFoundException($file_path);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getFileContent(string $subdirectory, string $file_name): string {
    $file_path = $this->getFile($subdirectory, $file_name);
    if (file_exists($file_path)) {
      $guide_directory = dirname($file_path);
      $content = str_replace('@guide_path', $guide_directory, file_get_contents($file_path));
      return $content;
    }
    else {
      throw new FileNotFoundException($file_path);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getLinks(): array {
    $guides_files = $this->getFiles();
    $guides = [];

    foreach ($guides_files as $md) {
      if (file_exists($md)) {
        $parts = pathinfo($md);
        $guides[] = Link::createFromRoute(str_replace('_', ' ', basename($parts['dirname'])), 'guides.guide', ['subdirectory' => basename($parts['dirname']), 'filename' => $parts['filename']])->toRenderable();
      }
    }

    return $guides;
  }

}
