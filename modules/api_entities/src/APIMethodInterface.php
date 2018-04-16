<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIExtDocRefInterface;
use Drupal\devportal_api_entities\Traits\APIParamRefInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\ConsumesInterface;
use Drupal\devportal_api_entities\Traits\ProducesInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIMethodInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, VendorExtensionInterface, ProducesInterface, ConsumesInterface, APIExtDocRefInterface, APIRefRefInterface, APIParamRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Method HTTP method.
   *
   * @return string
   *   The API Method HTTP method.
   */
  public function getHTTPMethod();

  /**
   * Sets the API Method HTTP method.
   *
   * @param $http_method
   *   The API Method HTTP method.
   * @return \Drupal\devportal_api_entities\APIMethodInterface
   *   The called API Method entity.
   */
  public function setHTTPMethod($http_method);

  /**
   * Gets the API Method description.
   *
   * @return string
   *   The API Method description.
   */
  public function getDescription();

  /**
   * Sets the API Method description.
   *
   * @param $description
   *   The API Method description.
   * @return \Drupal\devportal_api_entities\APIMethodInterface
   *   The called API Method entity.
   */
  public function setDescription($description);

}
