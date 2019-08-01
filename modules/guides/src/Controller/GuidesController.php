<?php

namespace Drupal\guides\Controller;

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

use Drupal\Core\Controller\ControllerBase;
use Drupal\guides\Exception\FileNotFoundException;
use Drupal\guides\GuidesStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * User guide page.
 */
class GuidesController extends ControllerBase {

  /**
   * The guides storage.
   *
   * @var \Drupal\guides\GuidesStorageInterface
   */
  protected $guidesStorage;

  /**
   * Creates a new HelpController.
   *
   * @param \Drupal\guides\GuidesStorageInterface $guides_storage
   *   The guides storage.
   */
  public function __construct(GuidesStorageInterface $guides_storage) {
    $this->guidesStorage = $guides_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('guides.guides_storage')
    );
  }

  /**
   * List guides.
   *
   * @return array
   *   Render array.
   */
  public function listGuides(): array {
    // Get the links of the files.
    $guides = $this->guidesStorage->getLinks();

    // If the guides array isn't empty, return its content as item list.
    if (!empty($guides)) {
      return [
        '#theme' => 'item_list',
        '#items' => $guides,
      ];
    }
    // Else return markup text.
    else {
      return [
        '#type' => 'html_tag',
        '#tag' => 'strong',
        '#value' => $this->t('No guides found.'),
      ];
    }
  }

  /**
   * Page callback that renders a markdown file on the UI.
   *
   * @param string $subdirectory
   *   The name of the subdirectory which contains the guide file.
   * @param string $filename
   *   The name of the guide file.
   *
   * @return array
   *   Render array.
   *
   * @throws \Drupal\guides\Exception\FileNotFoundException
   *   Thrown when no guide file is found.
   */
  public function guideContent(string $subdirectory, string $filename): array {
    try {
      // Parses the guide file.
      $md = new \Parsedown();
      $md = $md->text($this->guidesStorage->getFileContent($subdirectory, $filename));
    }
    catch (FileNotFoundException $e) {
      throw new FileNotFoundException($this->guidesStorage->getFile($subdirectory, $filename));
    }

    return [
      '#markup' => $md,
      '#attached' => [
        'library' => [
          'guides/guide',
          'guides/in_page_navigation',
        ],
      ],
    ];
  }

}
