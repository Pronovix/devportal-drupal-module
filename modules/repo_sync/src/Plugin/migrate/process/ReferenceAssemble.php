<?php

namespace Drupal\devportal_repo_sync\Plugin\migrate\process;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Replaces internal links in a HTML fragment to entity links.
 *
 * @MigrateProcessPlugin(
 *   id = "reference_assemble",
 * )
 */
class ReferenceAssemble extends ReferenceProcess implements MigrateProcessInterface, ContainerFactoryPluginInterface {

  /**
   * A map in tag => entity_name format.
   *
   * This tells the plugin what tag's attributes should be converted into what
   * entity's link.
   *
   * @var array
   */
  protected $tagEntityMap;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tagEntityMap = isset($configuration['tagEntityMap']) ? $configuration['tagEntityMap'] : [];
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var EntityTypeManager $entityTypeManager */
    $entityTypeManager = $container->get('entity_type.manager');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entityTypeManager
    );
  }

  /**
   * Replaces internal links in a HTML fragment.
   *
   * @param array $linkmap
   *   Map of internal link => entity id.
   * @param string $content
   *   HTML fragment.
   *
   * @return string
   */
  protected function assembleLinks(array $linkmap, $content) {
    return static::withLinks($content, function ($link, \DOMElement $element) use ($linkmap) {
      if (isset($linkmap[$link])) {
        $linkData = $linkmap[$link];
        $tagName = strtolower($element->tagName);
        if (isset($this->tagEntityMap[$tagName])) {
          $entity_type = $this->tagEntityMap[$tagName];
          $entity = $this->entityTypeManager->getStorage($entity_type)->load($linkData);
          // Using url() here instead of toUrl(), because file entity overrides
          // only url().
          $url = $entity->url();
          if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            $pos = strpos($url, '/', strlen('https://'));
            $url = substr($url, $pos);
          }

          return $url;
        }

        return $linkData;
      }

      return $link;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    list($content, $processed_links, $links) = $value;
    $linkmap = array_combine($links, $processed_links);
    return $this->assembleLinks($linkmap, $content);
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
