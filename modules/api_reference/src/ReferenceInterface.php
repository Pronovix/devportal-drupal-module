<?php

namespace Drupal\devportal_api_reference;

use Drupal\node\NodeInterface;

interface ReferenceInterface {

  public function getVersionFromAPIRef(NodeInterface $ref): ?string;

  public function getVersion(string $path): ?string;

  public function parse(string $file_path): ?array;

  public function validate(array $content);

  public function getTitle(string $path): ?string;

  public function getDescription(string $path): ?string;

}
