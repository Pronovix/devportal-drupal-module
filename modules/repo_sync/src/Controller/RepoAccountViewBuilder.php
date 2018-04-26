<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Utility\ThemeRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Shows a repository account entity.
 */
class RepoAccountViewBuilder extends EntityViewBuilder {

  /**
   * @var FormBuilderInterface
   */
  protected $form_builder;

  /**
   * {@inheritdoc}
   */
  public function __construct(FormBuilderInterface $form_builder, EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, Registry $theme_registry = NULL) {
    parent::__construct($entity_type, $entity_manager, $language_manager, $theme_registry);
    $this->form_builder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var FormBuilderInterface $form_builder */
    $form_builder = $container->get('form_builder');

    /** @var EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = $container->get('language_manager');

    /** @var ThemeRegistry $theme_registry */
    $theme_registry = $container->get('theme.registry');

    return new static($form_builder, $entity_type, $entity_manager, $language_manager, $theme_registry);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $defaults = parent::getBuildDefaults($entity, $view_mode);

    return $defaults;
  }

}
