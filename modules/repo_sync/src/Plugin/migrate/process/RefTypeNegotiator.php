<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\devportal_api_reference\Entity\APIRefType;
use Drupal\devportal_repo_sync\Entity\RepoImport;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "ref_type_negotiator",
 * )
 */
class RefTypeNegotiator extends ProcessPluginBase {

  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->config = $configuration['config'];
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    /** @var \Drupal\devportal_api_reference\Entity\APIRefType[] $types */
    $types = APIRefType::loadMultiple();
    foreach ($types as $type) {
      $extensions = NULL;
      switch (isset($this->config[$type->id()]) ? $this->config[$type->id()] : RepoImport::REF_IMPORT_SKIP) {
        case RepoImport::REF_IMPORT_FILTER:
          $extensions = $type->filtered_extensions;
          break;

        case RepoImport::REF_IMPORT_ALL:
          $extensions = $type->common_extensions;
          break;
      }

      if ($extensions) {
        $regexp = '/\.(?:' . implode('|', array_map('preg_quote', $extensions)) . ')$/';

        if (preg_match($regexp, $value)) {
          return $type->id();
        }
      }
    }

    return NULL;
  }

}
