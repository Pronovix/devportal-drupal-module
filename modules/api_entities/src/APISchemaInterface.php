<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\APIExtDocRefInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APISchemaInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, APIExtDocRefInterface, APIRefRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Schema ID.
   *
   * @return string
   *   The API Schema ID.
   */
  public function getSchemaID();

  /**
   * Sets the API Schema ID.
   *
   * @param $schema_id
   *   The API Schema ID.
   * @return \Drupal\devportal_api_entities\APISchemaInterface
   *   The called API Schema entity.
   */
  public function setSchemaID($schema_id);

}
