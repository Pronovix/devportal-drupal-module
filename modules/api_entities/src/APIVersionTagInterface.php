<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;

interface APIVersionTagInterface extends ContentEntityInterface, EntityChangedInterface, AutoLabelInterface, APIRefRefInterface {

  /**
   * Gets the API Version Tag name.
   *
   * @return string
   *   The API Version Tag name.
   */
  public function getName();

  /**
   * Sets the API Version Tag name.
   *
   * @param $name
   *   The API Version Tag name.
   * @return \Drupal\devportal_api_entities\APIVersionTagInterface
   *   The called API Version Tag entity.
   */
  public function setName($name);

}
