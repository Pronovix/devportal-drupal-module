<?php

namespace Drupal\devportal_api_reference\Plugin\Reference;

use Drupal\devportal_api_reference\ReferenceInterface;
use Drupal\node\NodeInterface;

abstract class ReferenceBase implements ReferenceInterface {

  protected function getSourcePath(NodeInterface $ref): ?string {
    /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $source */
    $source = $ref->get('field_source_file');
    /** @var \Drupal\file\Entity\File[] $referenced */
    $referenced = $source->referencedEntities();
    if (count($referenced) === 0) {
      return NULL;
    }
    $referenced_file = reset($referenced);
    return $referenced_file->getFileUri();
  }

  public function getVersionFromAPIRef(NodeInterface $ref): ?string {
    $path = $this->getSourcePath($ref);
    return $this->getVersion($path);
  }

}
