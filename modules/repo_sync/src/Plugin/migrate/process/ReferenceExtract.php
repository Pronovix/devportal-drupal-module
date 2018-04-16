<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extracts internal links from a HTML fragment.
 *
 * @MigrateProcessPlugin(
 *   id = "reference_extract",
 * )
 */
class ReferenceExtract extends ReferenceProcess implements MigrateProcessInterface, ContainerFactoryPluginInterface {

  /**
   * Regexp filter on the links.
   *
   * If this filter is not null, only links matching this filter will be
   * processed.
   *
   * @var string
   */
  protected $filter;

  /**
   * Link pattern to the remote file's location.
   *
   * @var string
   */
  protected $linkPattern;

  /**
   * Link pattern to the remote file's download location.
   *
   * @var string
   */
  protected $rawLinkPattern;

  /**
   * Migrate destination name where the extracted links will be saved.
   *
   * @var string
   */
  protected $linkDestination;

  /**
   * @var ConfigFactory
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->filter = isset($configuration['filter']) ? $configuration['filter'] : NULL;
    $this->linkPattern = isset($configuration['linkPattern']) ? $configuration['linkPattern'] : NULL;
    $this->rawLinkPattern = isset($configuration['rawLinkPattern']) ? $configuration['rawLinkPattern'] : NULL;
    $this->linkDestination = isset($configuration['linkDestination']) ? $configuration['linkDestination'] : 'links';
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var ConfigFactory $config */
    $config = $container->get('config.factory');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $config
    );
  }

  /**
   * Creates a link to a remote file.
   *
   * @param string $absPath
   *
   * @return string
   */
  protected function linkTo($absPath) {
    $rawPattern = $this->config->get('devportal_repo_sync.import')->get('raw_files');
    return strtr(
      preg_match($rawPattern, $absPath) ? $this->rawLinkPattern : $this->linkPattern,
      [
        '{path}' => $absPath,
      ]
    );
  }

  /**
   * Extracts links from a HTML fragment.
   *
   * @param string $filename
   *   Filename of the HTML file.
   * @param string $content
   *   HTML fragment.
   *
   * @return array
   */
  protected function extractLinks($filename, $content) {
    $links = [];

    $content = static::withLinks($content, function ($link) use (&$links, $filename) {
      if (preg_match('/^https?:\/\//', $link)) {
        return $link;
      }

      $absPath = $link[0] === '/' ?
        $link :
        static::resolveLink($filename, $link);

      if ($this->filter && preg_match($this->filter, $filename)) {
        $links[] = $absPath;
        return $absPath;
      }

      return $this->linkTo($absPath);
    });

    return [$content, array_unique($links)];
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $filename = $row->getSourceProperty('filename');
    list($content, $links) = $this->extractLinks($filename, $value);
    $row->setDestinationProperty($this->linkDestination, $links);
    return $content;
  }

}
