<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;

interface APIMetaParamInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, APIRefRefInterface, APIVersionTagRefInterface, VendorExtensionInterface {

  /**
   * Gets the API Meta Parameter name.
   *
   * @return string
   *   The API Meta Parameter name.
   */
  public function getName();

  /**
   * Sets the API Meta Parameter name.
   *
   * @param $name
   *   The API Meta Parameter name.
   * @return \Drupal\devportal_api_entities\APIMetaParamInterface
   *   The called API Meta Parameter entity.
   */
  public function setName($name);

}
