<?php

namespace Drupal\devportal_api_reference\Plugin\Reference;

use Drupal\Core\Plugin\PluginBase;
use Drupal\devportal_api_reference\ReferenceInterface;
use Drupal\node\NodeInterface;

/**
 * Base class for reference handlers.
 */
abstract class ReferenceBase extends PluginBase implements ReferenceInterface {

  /**
   * Returns the source path from the 'field_source_file' on a node.
   *
   * @param \Drupal\node\NodeInterface $ref
   *   A node of api_reference content type.
   *
   * @return null|string
   *   Source path if found, null if the field is empty.
   */
  protected function getSourcePath(NodeInterface $ref): ?string {
    /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $source */
    $source = $ref->get('field_source_file');
    /** @var \Drupal\file\Entity\File[] $referenced */
    $referenced = $source->referencedEntities();
    if (count($referenced) === 0) {
      return NULL;
    }
    $referenced_file = reset($referenced);
    return (string) $referenced_file->getFileUri();
  }

  /**
   * {@inheritdoc}
   */
  public function getVersionFromApiRef(NodeInterface $ref): ?string {
    $path = $this->getSourcePath($ref);
    $doc = $this->parse($path);
    return $this->getVersion($doc);
  }

}
