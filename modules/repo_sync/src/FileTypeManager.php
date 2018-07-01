<?php

namespace Drupal\devportal_repo_sync;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\devportal_repo_sync\Annotation\FileType;

/**
 * Manager service for the FileType plugins.
 */
class FileTypeManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cacheBackend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/FileType',
      $namespaces,
      $module_handler,
      FileTypeInterface::class,
      FileType::class
    );

    $this->alterInfo('file_type_info');
    $this->setCacheBackend($cacheBackend, 'file_type_plugins');
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions(): array {
    $definitions = parent::findDefinitions();
    uasort($definitions, function (array $def0, array $def1) {
      return ($def0['weight'] ?? 0) <=> ($def1['weight'] ?? 0);
    });

    return $definitions;
  }

  /**
   * Tries to find a plugin instance for a given filename.
   *
   * @param string $filename
   *   The imported file's name.
   *
   * @return \Drupal\devportal_repo_sync\FileTypeInterface|null
   *   Plugin instance if found, null otherwise.
   */
  public function lookupPlugin(string $filename): ?FileTypeInterface {
    $definitions = $this->getDefinitions();

    foreach ($definitions as $name => $definition) {
      $matcher = $definition['matcher'] ?? NULL;

      if (!$matcher) {
        continue;
      }

      if (preg_match($matcher, $filename)) {
        /** @var \Drupal\devportal_repo_sync\FileTypeInterface $instance */
        $instance = $this->createInstance($name);
        return $instance;
      }
    }

    return NULL;
  }

}
