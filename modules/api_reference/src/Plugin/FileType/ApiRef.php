<?php

namespace Drupal\devportal_api_reference\Plugin\FileType;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\devportal_repo_sync\Plugin\FileType\FileTypeBase;
use Drupal\node\Entity\Node;
use Psr\Http\Message\StreamInterface;

/**
 * @FileType(
 *   id = "api_ref",
 *   label = @Translation("API Reference"),
 *   matcher = "#(swagger|openapi)\.(ya?ml|json)$#",
 *   weight = 0,
 * )
 */
class ApiRef extends FileTypeBase {

  /**
   * {@inheritdoc}
   */
  public function import(?EntityInterface $entity, string $filename, string $prefix, StreamInterface $content): ?EntityInterface {
    $path = rtrim($prefix, '/') . '/' . $filename;

    if ($entity === NULL) {
      /** @var \Drupal\node\Entity\Node $entity */
      $entity = Node::create([
        'type' => 'api_reference',
      ]);
      $entity->setOwnerId($this->currentUser->id());
      $entity->set('path', $path);
    }

    $data = $content->getContents();

    if ($this->filesAreTheSame($data, $entity->field_source_file)) {
      return NULL;
    }

    $uri = $this->getFileUriForField($entity->field_source_file, $path);
    if (!$this->ensurePathForFile($uri)) {
      return NULL;
    }
    $file = file_save_data($data, $uri, FILE_EXISTS_RENAME);
    if (!$file) {
      return NULL;
    }

    list($title, $version, $description) = _devportal_api_reference_get_data_from_file($file->getFileUri());

    if (devportal_api_reference_check_api_version($entity, $version)) {
      $this->logger->error('This version has been added before');
      return NULL;
    }

    $entity->field_source_file->setValue($file);
    $violations = $entity->field_source_file->validate();
    if (count($violations)) {
      foreach ($violations as $violation) {
        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
        $this->logger->error($violation->getMessage());
      }

      return NULL;
    }

    $entity->setTitle($title);
    $entity->set('field_version', $version);
    $entity->set('field_description', $description);

    return $entity;
  }

}
