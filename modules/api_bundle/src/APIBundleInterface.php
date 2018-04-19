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
   * Gets the API Bundle image.
   *
   * @return integer
   *   The API Bundle image.
   */
  public function getImage();

  /**
   * Sets the API Bundle image.
   *
   * @param $image
   *   The API Bundle image.
   * @return \Drupal\devportal_api_bundle\APIBundleInterface
   *   The called API Bundle entity.
   */
  public function setImage($image);

  /**
   * Gets the API reference list.
   *
   * @return array
   *   An array of API IDs.
   */
  public function getAPIs();

  /**
   * Sets the API reference list.
   *
   * @param $apis
   *   An array of API IDs.
   * @return \Drupal\devportal_api_bundle\APIBundleInterface
   *   The called API Bundle entity.
   */
  public function setAPIs($apis);

  /**
   * Adds an API reference.
   *
   * @param $api
   *   An API ID.
   * @return \Drupal\devportal_api_bundle\APIBundleInterface
   *   The called API Bundle entity.
   */
  public function addAPI($api);

  /**
   * Gets the API Bundle author.
   *
   * @return string
   *   The API Bundle author.
   */
  public function getAuthor();

  /**
   * Sets the API Bundle author.
   *
   * @param $author
   *   The API Bundle author.
   * @return \Drupal\devportal_api_bundle\APIBundleInterface
   *   The called API Bundle entity.
   */
  public function setAuthor($author);

}
