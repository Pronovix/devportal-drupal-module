<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIInfoInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, VendorExtensionInterface, APIRefRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Info title.
   *
   * @return string
   *   The API Info title.
   */
  public function getTitle();

  /**
   * Sets the API Info title.
   *
   * @param $title
   *   The API Info title.
   * @return \Drupal\devportal_api_entities\APIInfoInterface
   *   The called API Info entity.
   */
  public function setTitle($title);

  /**
   * Gets the API Info description.
   *
   * @return string
   *   The API Info description.
   */
  public function getDescription();

  /**
   * Sets the API Info description.
   *
   * @param $description
   *   The API Info description.
   * @return \Drupal\devportal_api_entities\APIInfoInterface
   *   The called API Info entity.
   */
  public function setDescription($description);

  /**
   * Gets the API Info terms of service.
   *
   * @return string
   *   The API Info terms of service.
   */
  public function getTermsOfService();

  /**
   * Sets the API Info terms of service.
   *
   * @param $terms_of_service
   *   The API Info terms of service.
   * @return \Drupal\devportal_api_entities\APIInfoInterface
   *   The called API Info entity.
   */
  public function setTermsOfService($terms_of_service);

  /**
   * Gets the API Info version.
   *
   * @return string
   *   The API Info version.
   */
  public function getVersion();

  /**
   * Sets the API Info version.
   *
   * @param $version
   *   The API Info version.
   * @return \Drupal\devportal_api_entities\APIInfoInterface
   *   The called API Info entity.
   */
  public function setVersion($version);

}
