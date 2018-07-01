<?php

namespace Drupal\devportal_repo_sync\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class FileType extends Plugin {

  /**
   * Plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * Plugin label.
   *
   * @var string
   */
  public $label;

  /**
   * This regexp matches the file name.
   *
   * @var string
   */
  public $matcher;

  /**
   * Weight of the plugin.
   *
   * @var int
   */
  public $weight;

}
