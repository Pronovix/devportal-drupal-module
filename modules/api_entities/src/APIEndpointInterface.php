<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\APIParamRefInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIEndpointInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, VendorExtensionInterface, APIRefRefInterface, APIParamRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Endpoint uri.
   *
   * @return string
   *   The API Endpoint uri.
   */
  public function getUri();

  /**
   * Sets the API Endpoint uri.
   *
   * @param $uri
   *   The API Endpoint uri.
   * @return \Drupal\devportal_api_entities\APIEndpointInterface
   *   The called API Endpoint entity.
   */
  public function setUri($uri);

}
