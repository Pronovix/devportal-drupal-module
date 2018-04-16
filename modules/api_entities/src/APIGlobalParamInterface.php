<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\APIDocRefInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIGlobalParamInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, APIDocRefInterface, APIRefRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Global Parameter name.
   *
   * @return string
   *   The API Global Parameter name.
   */
  public function getName();

  /**
   * Sets the API Global Parameter name.
   *
   * @param $name
   *   The API Global Parameter name.
   * @return \Drupal\devportal_api_entities\APIGlobalParamInterface
   *   The called API Global Parameter entity.
   */
  public function setName($name);

}
