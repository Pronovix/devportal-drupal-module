<?php

namespace Drupal\devportal_api_reference\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class Reference extends Plugin {

  public $id;

  public $label;

  public $extensions;

  public $weight;

}
