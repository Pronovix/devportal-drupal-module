<?php

namespace Drupal\devportal_api_reference;

use Drupal\node\NodeInterface;

/**
 * A handler class for an API reference type.
 */
interface ReferenceInterface {

  /**
   * Returns a version of the API reference from a node.
   *
   * @param \Drupal\node\NodeInterface $ref
   *   The api_reference node that contains the api reference file.
   *
   * @return null|string
   *   Version, or null on error.
   */
  public function getVersionFromApiRef(NodeInterface $ref): ?string;

  /**
   * Returns a version of the API reference.
   *
   * @param string $path
   *   Path of the reference file.
   *
   * @return null|string
   *   Version, or null on error.
   */
  public function getVersion(string $path): ?string;

  /**
   * Returns the raw data of the reference.
   *
   * @param string $file_path
   *   Path of the reference file.
   *
   * @return object|null
   *   Raw data or null if invalid.
   */
  public function parse(string $file_path): ?object;

  /**
   * Validates the parsed content of an API reference.
   *
   * @param object $content
   *   Content from ReferenceInterface::parse().
   *
   * @throws \Exception
   *   Thrown on validation failure.
   */
  public function validate(object $content);

  /**
   * Returns the title of the API reference.
   *
   * @param string $path
   *   Path of the reference file.
   *
   * @return null|string
   *   Title, or null on error.
   */
  public function getTitle(string $path): ?string;

  /**
   * Returns the description of the API reference.
   *
   * @param string $path
   *   Path of the reference file.
   *
   * @return null|string
   *   Description, or null on error.
   */
  public function getDescription(string $path): ?string;

}
