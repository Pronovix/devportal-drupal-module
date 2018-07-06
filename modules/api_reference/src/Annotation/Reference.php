<?php

namespace Drupal\devportal_api_reference\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class Reference extends Plugin {

  /**
   * Machine name.
   *
   * @var string
   */
  public $id;

  /**
   * Human-readable label.
   *
   * @var string
   */
  public $label;

  /**
   * List of extensions where this plugin should be used.
   *
   * @var string[]
   */
  public $extensions;

  /**
   * Priority of the plugin.
   *
   * @var int
   */
  public $weight;

}
