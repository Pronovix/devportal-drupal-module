<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\APIDocRefInterface;
use Drupal\devportal_api_entities\Traits\APIExtDocRefInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIGlobalSchemaInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, APIDocRefInterface, APIRefRefInterface, APIExtDocRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Global Schema name.
   *
   * @return string
   *   The API Global Schema name.
   */
  public function getName();

  /**
   * Sets the API Global Schema name.
   *
   * @param $name
   *   The API Global Schema name.
   * @return \Drupal\devportal_api_entities\APIGlobalSchemaInterface
   *   The called API Global Schema entity.
   */
  public function setName($name);

  /**
   * Gets the API Global Schema value.
   *
   * @return string
   *   The API Global Schema value (as a serialized JSON blob).
   */
  public function getValue();

  /**
   * Sets the API Global Schema value.
   *
   * @param $value
   *   The API Global Schema value (as a serialized JSON blob).
   * @return \Drupal\devportal_api_entities\APIGlobalSchemaInterface
   *   The called API Global Schema entity.
   */
  public function setValue($value);

}
