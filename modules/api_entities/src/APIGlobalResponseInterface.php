<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\APIDocRefInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;

interface APIGlobalResponseInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, APIDocRefInterface, APIRefRefInterface, APIVersionTagRefInterface, VendorExtensionInterface {

  /**
   * Gets the API Global Response name.
   *
   * @return string
   *   The API Global Response name.
   */
  public function getName();

  /**
   * Sets the API Global Response name.
   *
   * @param $name
   *   The API Global Response name.
   * @return \Drupal\devportal_api_entities\APIGlobalResponseInterface
   *   The called API Global Response entity.
   */
  public function setName($name);

  /**
   * Gets the API Global Response description.
   *
   * @return string
   *   The API Global Response description.
   */
  public function getDescription();

  /**
   * Sets the API Global Response description.
   *
   * @param $description
   *   The API Global Response description.
   * @return \Drupal\devportal_api_entities\APIGlobalResponseInterface
   *   The called API Global Response entity.
   */
  public function setDescription($description);

}
