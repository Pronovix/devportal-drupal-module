<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Converts various formats to HTML.
 *
 * @MigrateProcessPlugin(
 *   id = "smart_converter",
 * )
 */
class SmartConverter extends ProcessPluginBase implements MigrateProcessInterface, ContainerFactoryPluginInterface {

  /**
   * @var array
   */
  protected $fileTypes;

  /**
   * @var \Drupal\migrate\Plugin\MigratePluginManager
   */
  protected $processPluginManager;

  protected $commonPluginConfig = [];

  protected $pluginConfigs = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigratePluginManager $processPluginManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    if (isset($configuration['commonPluginConfig'])) {
      $this->commonPluginConfig = $configuration['commonPluginConfig'];
    }

    if (isset($configuration['pluginConfigs'])) {
      $this->pluginConfigs = $configuration['pluginConfigs'];
    }

    $this->fileTypes = $configuration['fileTypes'];
    $this->processPluginManager = $processPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\migrate\Plugin\MigratePluginManager $processPluginManager */
    $processPluginManager = $container->get('plugin.manager.migrate.process');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $processPluginManager
    );
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    list($filename, $content) = $value;
    if (!($converterPlugin = $this->findConverterPlugin($filename))) {
      throw new MigrateException($this->t("Can't find a converter plugin for @filename", [
        '@filename' => $filename,
      ]));
    }

    $configuration = $this->commonPluginConfig + (isset($this->pluginConfigs[$converterPlugin]) ? $this->pluginConfigs[$converterPlugin] : []);

    /** @var MigrateProcessInterface $processPlugin */
    $processPlugin = $this->processPluginManager->createInstance($converterPlugin, $configuration);

    return $processPlugin->transform($content, $migrate_executable, $row, $destination_property);
  }

  /**
   * Finds the appropriate converter plugin for a given file.
   *
   * @param string $filename
   *   Filename.
   *
   * @return string|bool
   *   Converter plugin or false if not found.
   */
  protected function findConverterPlugin($filename) {
    foreach ($this->fileTypes as $data) {
      $extensions = implode('|', $data['extensions']);
      if (preg_match("/\.($extensions)/", $filename)) {
        return $data['converterPlugin'];
      }
    }

    return FALSE;
  }

}
