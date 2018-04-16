<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIResponseInterface extends ContentEntityInterface, EntityChangedInterface, VendorExtensionInterface, RevisionLogInterface, AutoLabelInterface, APIRefRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Response code.
   *
   * @return string
   *   The API Response code.
   */
  public function getCode();

  /**
   * Sets the API Response code.
   *
   * @param $code
   *   The API Response code.
   * @return \Drupal\devportal_api_entities\APIResponseInterface
   *   The called API Response entity.
   */
  public function setCode($code);

  /**
   * Gets the API Response description.
   *
   * @return string
   *   The API Response description.
   */
  public function getDescription();

  /**
   * Sets the API Response description.
   *
   * @param $description
   *   The API Response description.
   * @return \Drupal\devportal_api_entities\APIResponseInterface
   *   The called API Response entity.
   */
  public function setDescription($description);

}
