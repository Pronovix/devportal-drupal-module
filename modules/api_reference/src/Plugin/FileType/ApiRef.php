<?php

namespace Drupal\devportal_api_reference\Plugin\FileType;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Utility\Token;
use Drupal\devportal_repo_sync\Plugin\FileType\FileTypeBase;
use Drupal\node\Entity\Node;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

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
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $guesser = $container->get('file.mime_type.guesser');
    $fileSystem = $container->get('file_system');
    $currentUser = $container->get('current_user');
    $token = $container->get('token');
    $logger = $container->get('logger.factory')->get('file_import');
    $moduleHandler = $container->get('module_handler');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $guesser,
      $fileSystem,
      $currentUser,
      $token,
      $logger,
      $moduleHandler
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, MimeTypeGuesserInterface $guesser, FileSystem $fileSystem, AccountProxyInterface $currentUser, Token $token, LoggerChannelInterface $logger, ModuleHandlerInterface $moduleHandler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $guesser, $fileSystem, $currentUser, $token, $logger);
    $this->moduleHandler = $moduleHandler;
  }

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

    list($title, $version, $description, $doc, $type) = _devportal_api_reference_get_data_from_file($file->getFileUri());

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

    $mappings = $this->moduleHandler
      ->invokeAll('devportal_api_reference_fields', [$type, $doc, $file]);
    foreach ($mappings as $field_name => $value) {
      $entity->set($field_name, $value);
    }

    return $entity;
  }

}
