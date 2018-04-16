<?php

namespace Drupal\devportal_repo_sync;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\migrate\Plugin\MigrateSourcePluginManager;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Overrides the local task title for the 'redirect to edit' local task.
 */
class ImportedContentRedirectToEditLocalTask extends LocalTaskDefault {

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request = NULL, NodeInterface $node = NULL) {
    if ($node->getType() !== 'imported_content') {
      return NULL;
    }

    /** @var MigrateSourcePluginManager $source_plugin_manager */
    $source_plugin_manager = \Drupal::service('plugin.manager.migrate.source');

    $provider = $node->get('field_ic_provider')->getString();
    $definition = $source_plugin_manager->getDefinition($provider);

    return new TranslatableMarkup('Edit on @label', [
      '@label' => $definition['label'],
    ]);
  }

}
