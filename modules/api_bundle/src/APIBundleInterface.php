<?php

namespace Drupal\devportal_api_bundle;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;

interface APIBundleInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface {

  /**
   * Gets the API Bundle title.
   *
   * @return string
   *   The API Bundle title.
   */
  public function getTitle();

  /**
   * Sets the API Bundle title.
   *
   * @param $title
   *   The API Bundle title.
   * @return \Drupal\devportal_api_bundle\APIBundleInterface
   *   The called API Bundle entity.
   */
  public function setTitle($title);

  /**
   * Gets the API Bundle description.
   *
   * @return string
   *   The API Bundle description.
   */
  public function getDescription();

  /**
   * Sets the API Bundle description.
   *
   * @param $description
   *   The API Bundle description.
   * @return \Drupal\devportal_api_bundle\APIBundleInterface
   *   The called API Bundle entity.
   */
  public function setDescription($description);

  /**
   * Gets the API reference list.
   *
   * @return array
   *   An array of API IDs.
   */
  public function getAPIRefs();

  /**
   * Sets the API reference list.
   *
   * @param $api_refs
   *   An array of API Reference IDs.
   * @return \Drupal\devportal_api_bundle\APIBundleInterface
   *   The called API Bundle entity.
   */
  public function setAPIRefs($api_refs);

  /**
   * Adds an API reference.
   *
   * @param $api_ref
   *   An API Reference ID.
   * @return \Drupal\devportal_api_bundle\APIBundleInterface
   *   The called API Bundle entity.
   */
  public function addAPIRef($api_ref);

}
