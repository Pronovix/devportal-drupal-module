<?php

namespace Drupal\devportal_api_entities\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\devportal_api_entities\APIResponseHeaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\devportal_api_entities\APIResponseHeaderStorageInterface;

/**
 * Returns responses for API Response Header routes.
 *
 * @package Drupal\devportal_api_entities\Controller
 */
class APIResponseHeaderController extends ControllerBase {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs an APIResponseHeaderController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   */
  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var DateFormatterInterface $dateFormatter */
    $dateFormatter = $container->get('date.formatter');
    /** @var RendererInterface $renderer */
    $renderer = $container->get('renderer');
    /** @var EntityTypeBundleInfoInterface $entityTypeBundleInfo */
    $entityTypeBundleInfo = $container->get('entity_type.bundle.info');
    return new static($dateFormatter, $renderer, $entityTypeBundleInfo);
  }

  /**
   * Displays an API Response Header revision.
   *
   * @param int $api_response_header_revision
   *   The API Response Header revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function revisionShow($api_response_header_revision) {
    $api_response_header = $this->entityTypeManager()->getStorage('api_response_header')->loadRevision($api_response_header_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('api_response_header');
    return $view_builder->view($api_response_header);
  }

  /**
   * Page title callback for an API Response Header revision.
   *
   * @param int $api_response_header_revision
   *   The API Response Header revision ID.
   *
   * @return string
   *   The page title.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function revisionPageTitle($api_response_header_revision) {
    /** @var \Drupal\devportal_api_entities\Entity\APIResponseHeader $api_response_header */
    $api_response_header = $this->entityTypeManager()->getStorage('api_response_header')->loadRevision($api_response_header_revision);
    return $this->t('Revision of %title from %date', ['%title' => $api_response_header->label(), '%date' => $this->dateFormatter->format($api_response_header->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a API Response Header.
   *
   * @param \Drupal\devportal_api_entities\APIResponseHeaderInterface $api_response_header
   *   An API Response Header object.
   *
   * @return array
   *   An array as expected by drupal_render().
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function revisionOverview(APIResponseHeaderInterface $api_response_header) {
    $account = $this->currentUser();
    $langcode = $api_response_header->language()->getId();
    $langname = $api_response_header->language()->getName();
    $languages = $api_response_header->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    /** @var \Drupal\devportal_api_entities\APIResponseHeaderStorageInterface $api_response_header_storage */
    $api_response_header_storage = $this->entityTypeManager()->getStorage('api_response_header');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $api_response_header->label()]) : $this->t('Revisions for %title', ['%title' => $api_response_header->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = ($account->hasPermission("revert all api response header revisions") || $account->hasPermission('administer api response headers'));
    $delete_permission = ($account->hasPermission("delete all api response header revisions") || $account->hasPermission('administer api response headers'));

    $rows = [];
    $default_revision = $api_response_header->getRevisionId();

    foreach ($this->getRevisionIds($api_response_header, $api_response_header_storage) as $vid) {
      /** @var \Drupal\devportal_api_entities\APIResponseHeaderInterface $revision */
      $revision = $api_response_header_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $api_response_header->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.api_response_header.revision', ['api_response_header' => $api_response_header->id(), 'api_response_header_revision' => $vid]))->toString();
        }
        else {
          $link = $api_response_header->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        // @todo Simplify once https://www.drupal.org/node/2334319 lands.
        $this->renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($vid == $default_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];

          $rows[] = [
            'data' => $row,
            'class' => ['revision-current'],
          ];
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $vid < $api_response_header->getRevisionId() ? $this->t('Revert') : $this->t('Set as current revision'),
              'url' => $has_translations ?
                Url::fromRoute('entity.api_response_header.translation_revert', ['api_response_header' => $api_response_header->id(), 'api_response_header_revision' => $vid, 'langcode' => $langcode]) :
                Url::fromRoute('entity.api_response_header.revision_revert', ['api_response_header' => $api_response_header->id(), 'api_response_header_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.api_response_header.revision_delete', ['api_response_header' => $api_response_header->id(), 'api_response_header_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          $rows[] = $row;
        }
      }
    }

    $build['api_response_header_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['class' => 'api-response-header-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * Gets a list of API Response Header revision IDs for a specific API Response Header.
   *
   * @param \Drupal\devportal_api_entities\APIResponseHeaderInterface $api_response_header
   *   The API Response Header entity.
   * @param \Drupal\devportal_api_entities\APIResponseHeaderStorageInterface $api_response_header_storage
   *   The API Response Header storage handler.
   *
   * @return int[]
   *   API Response Header revision IDs (in descending order).
   */
  protected function getRevisionIds(APIResponseHeaderInterface $api_response_header, APIResponseHeaderStorageInterface $api_response_header_storage) {
    $result = $api_response_header_storage->getQuery()
      ->allRevisions()
      ->condition($api_response_header->getEntityType()->getKey('id'), $api_response_header->id())
      ->sort($api_response_header->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }

  /**
   * Displays add links for the available bundles.
   *
   * Redirects to the add form if there's only one bundle available.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
   *   If there's only one available bundle, a redirect response.
   *   Otherwise, a render array with the add links for each bundle.
   *
   * @see \Drupal\Core\Entity\Controller\EntityController::addPage()
   * The only changes to that method are:
   * - We want to have the original label, instead of the lowercased one.
   * - ControllerBase::entityTypeManager() should be used through this method,
   *   whilst EntityController->entityTypeManager is dependency-injected, so it
   *   can be used directly there.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function addPage($entity_type_id) {
    $entity_type = $this->entityTypeManager()->getDefinition($entity_type_id);
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
    $bundle_key = $entity_type->getKey('bundle');
    $bundle_entity_type_id = $entity_type->getBundleEntityType();
    $build = [
      '#theme' => 'entity_add_list',
      '#bundles' => [],
    ];
    if ($bundle_entity_type_id) {
      $bundle_argument = $bundle_entity_type_id;
      $bundle_entity_type = $this->entityTypeManager()->getDefinition($bundle_entity_type_id);
      $bundle_entity_type_label = $bundle_entity_type->getLabel();
      $build['#cache']['tags'] = $bundle_entity_type->getListCacheTags();

      // Build the message shown when there are no bundles.
      $link_text = $this->t('Add a new @entity_type.', ['@entity_type' => $bundle_entity_type_label]);
      $link_route_name = 'entity.' . $bundle_entity_type->id() . '.add_form';
      $build['#add_bundle_message'] = $this->t('There is no @entity_type yet. @add_link', [
        '@entity_type' => $bundle_entity_type_label,
        '@add_link' => Link::createFromRoute($link_text, $link_route_name)->toString(),
      ]);
      // Filter out the bundles the user doesn't have access to.
      $access_control_handler = $this->entityTypeManager()->getAccessControlHandler($entity_type_id);
      foreach ($bundles as $bundle_name => $bundle_info) {
        $access = $access_control_handler->createAccess($bundle_name, NULL, [], TRUE);
        if (!$access->isAllowed()) {
          unset($bundles[$bundle_name]);
        }
        $this->renderer->addCacheableDependency($build, $access);
      }
      // Add descriptions from the bundles.
      $bundles = $this->loadBundleDescriptions($bundles, $bundle_entity_type);
    }
    else {
      $bundle_argument = $bundle_key;
    }

    $form_route_name = 'entity.' . $entity_type_id . '.add_form';
    // Redirect if there's only one bundle available.
    if (count($bundles) == 1) {
      $bundle_names = array_keys($bundles);
      $bundle_name = reset($bundle_names);
      return $this->redirect($form_route_name, [$bundle_argument => $bundle_name]);
    }
    // Prepare the #bundles array for the template.
    foreach ($bundles as $bundle_name => $bundle_info) {
      $build['#bundles'][$bundle_name] = [
        'label' => $bundle_info['label'],
        'description' => isset($bundle_info['description']) ? $bundle_info['description'] : '',
        'add_link' => Link::createFromRoute($bundle_info['label'], $form_route_name, [$bundle_argument => $bundle_name]),
      ];
    }

    return $build;
  }

  /**
   * Expands the bundle information with descriptions, if known.
   *
   * @param array $bundles
   *   An array of bundle information.
   * @param \Drupal\Core\Entity\EntityTypeInterface $bundle_entity_type
   *   The bundle entity type definition.
   *
   * @return array
   *   The expanded array of bundle information.
   *
   * @see \Drupal\Core\Entity\Controller\EntityController::loadBundleDescriptions()
   *   The only change to that method is:
   * - ControllerBase::entityTypeManager() should be used through this method,
   *   whilst EntityController->entityTypeManager is dependency-injected, so it
   *   can be used directly there.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function loadBundleDescriptions(array $bundles, EntityTypeInterface $bundle_entity_type) {
    if (!$bundle_entity_type->entityClassImplements(EntityDescriptionInterface::class)) {
      return $bundles;
    }
    $bundle_names = array_keys($bundles);
    $storage = $this->entityTypeManager()->getStorage($bundle_entity_type->id());
    /** @var \Drupal\Core\Entity\EntityDescriptionInterface[] $bundle_entities */
    $bundle_entities = $storage->loadMultiple($bundle_names);
    foreach ($bundles as $bundle_name => &$bundle_info) {
      if (isset($bundle_entities[$bundle_name])) {
        $bundle_info['description'] = $bundle_entities[$bundle_name]->getDescription();
      }
    }

    return $bundles;
  }

}
