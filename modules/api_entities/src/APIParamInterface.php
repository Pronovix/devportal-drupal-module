<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIParamInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, APIRefRefInterface, APIVersionTagRefInterface {

  /**
   * Gets the API Parameter name.
   *
   * @return string
   *   The API Parameter name.
   */
  public function getName();

  /**
   * Sets the API Parameter name.
   *
   * @param $name
   *   The API Parameter name.
   * @return \Drupal\devportal_api_entities\APIParamInterface
   *   The called API Parameter entity.
   */
  public function setName($name);

  /**
   * Gets the type of the API Parameter entity.
   *
   * The type is one of the API Meta Parameter entity's "param_in" field values.
   *
   * @return mixed
   *   The type of the API Parameter entity if available, NULL otherwise.
   *
   * @see \Drupal\devportal_api_entities\Entity\APIMetaParam::baseFieldDefinitions()
   */
  public function getParameterType();

}
