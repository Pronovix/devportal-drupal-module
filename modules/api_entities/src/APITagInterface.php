<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\APIDocRefInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\APIExtDocRefInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APITagInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, VendorExtensionInterface, APIExtDocRefInterface, APIDocRefInterface, APIRefRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Tag name.
   *
   * @return string
   *   The API Tag name.
   */
  public function getName();

  /**
   * Sets the API Tag name.
   *
   * @param $name
   *   The API Tag name.
   * @return \Drupal\devportal_api_entities\APITagInterface
   *   The called API Tag entity.
   */
  public function setName($name);

  /**
   * Gets the API Tag description.
   *
   * @return string
   *   The API Tag description.
   */
  public function getDescription();

  /**
   * Sets the API Tag description.
   *
   * @param $description
   *   The API Tag description.
   * @return \Drupal\devportal_api_entities\APITagInterface
   *   The called API Tag entity.
   */
  public function setDescription($description);

}
