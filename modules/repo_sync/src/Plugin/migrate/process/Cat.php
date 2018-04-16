<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\Core\File\FileSystem;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Copies a blob into a file.
 *
 * @MigrateProcessPlugin(
 *   id = "cat"
 * )
 */
class Cat extends ProcessPluginBase implements MigrateProcessInterface, ContainerFactoryPluginInterface {

  /**
   * @var FileSystem
   */
  protected $file_system;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileSystem $file_system) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->file_system = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    /** @var FileSystem $file_system */
    $file_system = $container->get('file_system');

    return new static($configuration, $plugin_id, $plugin_definition, $file_system);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $uri = $this->file_system->tempnam('temporary://', '');
    file_put_contents($uri, $value);
    return $uri;
  }

}
