<?php

namespace Drupal\devportal_api_entities\Plugin\migrate\process;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateProcessPlugin(
 *   id = "getcontents",
 * )
 */
class GetContents extends ProcessPluginBase implements MigrateProcessInterface, ContainerFactoryPluginInterface {

  /**
   * @var FileSystemInterface
   */
  protected $fileSystem;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileSystemInterface $file_system) {
    $configuration += [
      'rename' => FALSE,
    ];
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var FileSystemInterface $file_system */
    $file_system = $container->get('file_system');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $file_system
    );
  }

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($row->isStub()) {
      return NULL;
    }

    list($source, $destination) = $value;
    $replace = !empty($this->configuration['rename']) ?
      FILE_EXISTS_RENAME :
      FILE_EXISTS_REPLACE;
    $final_destination = file_destination($destination, $replace);

    $destination_stream = @fopen($final_destination, 'w');
    if (!$destination_stream) {
      $dir = $this->fileSystem->dirname($final_destination);
      if (!file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
        throw new MigrateException("Could not create or write to directory '$dir");
      }

      $destination_stream = @fopen($final_destination, 'w');
      if (!$destination_stream) {
        throw new MigrateException("Could not write to file '$final_destination");
      }
    }

    try {
      $contents = file_get_contents($source);
      fwrite($destination_stream, $contents);
      fclose($destination_stream);
    }
    catch (\Exception $e) {
      throw new MigrateException("{$e->getMessage()} ($source)");
    }

    return $final_destination;
  }

}
