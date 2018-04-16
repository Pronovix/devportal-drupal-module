<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIExtDocInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, VendorExtensionInterface, APIRefRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Ext Doc url.
   *
   * @return string
   *   The API Ext Doc url.
   */
  public function getURL();

  /**
   * Sets the API Ext Doc url.
   *
   * @param $url
   *   The API Ext Doc url.
   * @return \Drupal\devportal_api_entities\APIExtDocInterface
   *   The called API Ext Doc entity.
   */
  public function setURL($url);

  /**
   * Gets the API Ext Doc description.
   *
   * @return string
   *   The API Ext Doc description.
   */
  public function getDescription();

  /**
   * Sets the API Ext Doc description.
   *
   * @param $description
   *   The API Ext Doc description.
   * @return \Drupal\devportal_api_entities\APIExtDocInterface
   *   The called API Ext Doc entity.
   */
  public function setDescription($description);

}
