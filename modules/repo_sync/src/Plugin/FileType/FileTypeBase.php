<?php

namespace Drupal\devportal_repo_sync\Plugin\FileType;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Utility\Token;
use Drupal\devportal_repo_sync\FileTypeInterface;
use Drupal\file\Entity\File;
use Drupal\redirect\Entity\Redirect;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract base class for the FileType plugins.
 */
abstract class FileTypeBase extends PluginBase implements FileTypeInterface, ContainerFactoryPluginInterface {

  /**
   * The current_user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * File mime guesser.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $guesser;

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $guesser = $container->get('file.mime_type.guesser');
    $fileSystem = $container->get('file_system');
    $currentUser = $container->get('current_user');
    $token = $container->get('token');
    $logger = $container->get('logger.factory')->get('file_import');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $guesser,
      $fileSystem,
      $currentUser,
      $token,
      $logger
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    MimeTypeGuesserInterface $guesser,
    FileSystem $fileSystem,
    AccountProxyInterface $currentUser,
    Token $token,
    LoggerChannelInterface $logger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->guesser = $guesser;
    $this->fileSystem = $fileSystem;
    $this->currentUser = $currentUser;
    $this->token = $token;
    $this->logger = $logger;
  }

  /**
   * Makes sure that the file exists and contains the given content.
   *
   * @param string $filename
   *   File name.
   * @param string $prefix
   *   File prefix.
   * @param \Psr\Http\Message\StreamInterface $content
   *   File content.
   *
   * @return null|string
   *   The URI of the saved file, or NULL if an error happened.
   */
  protected function ensureFile(string $filename, string $prefix, StreamInterface $content): ?string {
    $uri = $this->getFileUri($filename, $prefix);
    if (!$this->ensurePathForFile($uri)) {
      return NULL;
    }
    if (file_put_contents($uri, $content) === FALSE) {
      return NULL;
    }
    if (!$this->fileSystem->chmod($uri)) {
      return NULL;
    }

    return $uri;
  }

  /**
   * Creates a directory for a given file.
   *
   * @param string $uri
   *   File name.
   *
   * @return bool
   *   TRUE if the directory exists or created without error.
   */
  protected function ensurePathForFile(string $uri): bool {
    $path = dirname($uri);
    if (!file_exists($path)) {
      if (!$this->fileSystem->mkdir($path, NULL, TRUE)) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Saves a file entity.
   *
   * @param string $filename
   *   File name.
   * @param string $prefix
   *   File prefix.
   * @param \Psr\Http\Message\StreamInterface $content
   *   File content.
   * @param int $status
   *   File status (temporary or permanent).
   * @param bool $create_redirect
   *   Whether to create a redirect for the file.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The created file entity.
   */
  protected function saveFile(string $filename, string $prefix, StreamInterface $content, int $status = FILE_STATUS_PERMANENT, bool $create_redirect = TRUE): ?EntityInterface {
    if (($uri = $this->ensureFile($filename, $prefix, $content)) === NULL) {
      return NULL;
    }

    /** @var \Drupal\file\Entity\File $file */
    $file = File::create([
      'uid' => $this->currentUser->id(),
      'status' => $status,
      'filename' => basename($filename),
      'uri' => $uri,
      'filesize' => $content->getSize(),
      'filemime' => $this->guesser->guess($filename),
    ]);

    $file->save();

    if ($create_redirect) {
      $url = file_create_url($file->getFileUri());
      $path = trim(parse_url($url, PHP_URL_PATH), '/');

      /** @var \Drupal\redirect\Entity\Redirect $redirect */
      $redirect = Redirect::create();
      $redirect->setSource(rtrim($prefix, '/') . '/' . ltrim($filename, '/'));
      // The `base` is needed to the URL, because for some reason Drupal removes
      // the first part after `internal://`.
      $redirect->redirect_redirect->set(0, [
        'uri' => "internal://base/{$path}",
        'options' => [],
      ]);
      $redirect->setStatusCode(Response::HTTP_TEMPORARY_REDIRECT);
      $redirect->save();
    }

    return $file;
  }

  /**
   * Constructs an URI for a given file.
   *
   * @param string $filename
   *   File name.
   * @param string $prefix
   *   File prefix.
   *
   * @return string
   *   The constructed URI.
   */
  protected function getFileUri(string $filename, string $prefix): string {
    $filename = trim($filename, '/');
    $prefix = trim($prefix, '/');
    return "public://import/{$prefix}/{$filename}";
  }

  /**
   * Constructs a url for a given filename.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field where the file will be.
   * @param string $filename
   *   The name of the file that will be saved.
   *
   * @return string
   *   The properly formatted file uri.
   */
  protected function getFileUriForField(FieldItemListInterface $field, string $filename): string {
    $settings = $field->getDataDefinition()->getSettings();

    $destination = "{$settings['uri_scheme']}://{$settings['file_directory']}/{$filename}";
    $destination = $this->token->replace($destination);

    return $destination;
  }

  /**
   * Checks if a file content and an existing file are the same.
   *
   * @param string $new
   *   New content.
   * @param \Drupal\Core\Field\FieldItemListInterface $old
   *   Existing file (in a file field).
   *
   * @return bool
   *   TRUE if the contents match.
   */
  protected function filesAreTheSame(string $new, FieldItemListInterface $old): bool {
    if ($old->count() === 0) {
      return FALSE;
    }

    /** @var \Drupal\file\Entity\File $file */
    $file = File::load($old->getValue()[0]['target_id']);

    return md5($new) === md5_file($file->getFileUri());
  }

}

