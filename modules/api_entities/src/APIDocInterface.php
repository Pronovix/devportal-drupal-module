<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_entities\Traits\ConsumesInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\APIExtDocRefInterface;
use Drupal\devportal_api_entities\Traits\ProducesInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;

interface APIDocInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, VendorExtensionInterface, ProducesInterface, ConsumesInterface, APIRefRefInterface, APIExtDocRefInterface, EntityPublishedInterface, APIVersionTagRefInterface {

  /**
   * Denotes that the API Documentation is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the API Documentation is published.
   */
  const PUBLISHED = 1;

  /**
   * Gets the API Doc host.
   *
   * @return string
   *   The API Doc host.
   */
  public function getHost();

  /**
   * Sets the API Doc host.
   *
   * @param $host
   *   The API Doc host.
   * @return \Drupal\devportal_api_entities\APIDocInterface
   *   The called API Doc entity.
   */
  public function setHost($host);

  /**
   * Gets the API Doc base path.
   *
   * @return string
   *   The API Doc base path.
   */
  public function getBasePath();

  /**
   * Sets the API Doc base path.
   *
   * @param $base_path
   *   The API Doc base path.
   * @return \Drupal\devportal_api_entities\APIDocInterface
   *   The called API Doc entity.
   */
  public function setBasePath($base_path);

  /**
   * Gets the API Doc protocol.
   *
   * @return string
   *   The API Doc protocol.
   */
  public function getProtocol();

  /**
   * Sets the API Doc protocol.
   *
   * @param $protocol
   *   The API Doc protocol.
   * @return \Drupal\devportal_api_entities\APIDocInterface
   *   The called API Doc entity.
   */
  public function setProtocol($protocol);

  /**
   * Gets the API Doc source file.
   *
   * @return integer
   *   The API Doc source file.
   */
  public function getSourceFile();

  /**
   * Sets the API Doc source file.
   *
   * @param $source_file
   *   The API Doc source file.
   * @return \Drupal\devportal_api_entities\APIDocInterface
   *   The called API Doc entity.
   */
  public function setSourceFile($source_file);

}
