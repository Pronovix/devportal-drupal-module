<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Converts a path into one of its component.
 *
 * @MigrateProcessPlugin(
 *   id = "pathinfo"
 * )
 */
class PathInfo extends ProcessPluginBase implements MigrateProcessInterface, ContainerFactoryPluginInterface {

  /**
   * @see \pathinfo()
   *
   * @var int
   */
  protected $options;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    static $options = [
      'dirname' => PATHINFO_DIRNAME,
      'basename' => PATHINFO_BASENAME,
      'extension' => PATHINFO_EXTENSION,
      'filename' => PATHINFO_FILENAME,
    ];

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->options = isset($options[$configuration['options']]) ? $options[$configuration['options']] : PATHINFO_FILENAME;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return pathinfo($value, $this->options);
  }

}
