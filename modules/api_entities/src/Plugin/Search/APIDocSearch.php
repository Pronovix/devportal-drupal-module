<?php

namespace Drupal\devportal_api_entities\Plugin\Search;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Config;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Render\RendererInterface;
use Drupal\search\Plugin\ConfigurableSearchPluginBase;
use Drupal\search\Plugin\SearchIndexingInterface;
use Drupal\Search\SearchQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\devportal_api_entities\APIDocInterface;

/**
 * Handles searching for API Documentation entities using the Search module index.
 *
 * @SearchPlugin(
 *   id = "api_doc_search",
 *   title = @Translation("API Documentation")
 * )
 */
class APIDocSearch extends ConfigurableSearchPluginBase implements AccessibleInterface, SearchIndexingInterface {

  /**
   * A database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * An entity manager object.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * A module manager object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * A config object for 'search.settings'.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $searchSettings;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Drupal account to use for checking for access to advanced search.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The Renderer service to format the username and API Documentation.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The list of options and info for advanced search filters.
   *
   * Each entry in the array has the option as the key and for its value, an
   * array that determines how the value is matched in the database query. The
   * possible keys in that array are:
   * - column: (required) Name of the database column to match against.
   * - join: (optional) Information on a table to join. By default the data is
   *   matched against the {api_doc_field_data} table.
   * - operator: (optional) OR or AND, defaults to OR.
   *
   * @var array
   */
  protected $advanced = [
    // TODO: These options can be different at custom entity types.
    'type' => ['column' => 'api_doc.type'],
    'language' => ['column' => 'i.langcode'],
    'author' => ['column' => 'api_doc.uid'],
  ];

  /**
   * A constant for setting and checking the query string.
   */
  const ADVANCED_FORM = 'advanced-form';

  /**
   * {@inheritdoc}
   */
  static public function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('module_handler'),
      $container->get('config.factory')->get('search.settings'),
      $container->get('language_manager'),
      $container->get('renderer'),
      $container->get('current_user')
    );
  }

  /**
   * Constructs a \Drupal\devportal_api_entities\Plugin\Search\APIDocSearch object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection object.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   An entity manager object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   A module manager object.
   * @param \Drupal\Core\Config\Config $search_settings
   *   A config object for 'search.settings'.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The $account object to use for checking for access to advanced search.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, Config $search_settings, LanguageManagerInterface $language_manager, RendererInterface $renderer, AccountInterface $account = NULL) {
    $this->database = $database;
    $this->entityManager = $entity_manager;
    $this->moduleHandler = $module_handler;
    $this->searchSettings = $search_settings;
    $this->languageManager = $language_manager;
    $this->renderer = $renderer;
    $this->account = $account;
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->addCacheTags(['api_doc_list']);
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'view', AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIfHasPermission($account, 'access content');
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function isSearchExecutable() {
    // API Documentation search is executable if we have keywords or an advanced
    // parameter. At least, we should parse out the parameters and see if there
    // are any keyword matches in that case, rather than just printing out the
    // "Please enter keywords" message.
    return !empty($this->keywords) || (isset($this->searchParameters['f']) && count($this->searchParameters['f']));
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    if ($this->isSearchExecutable()) {
      $results = $this->findResults();

      if ($results) {
        return $this->prepareResults($results);
      }
    }

    return [];
  }

  /**
   * Queries to find search results, and sets status messages.
   *
   * This method can assume that $this->isSearchExecutable() has already been
   * checked and returned TRUE.
   *
   * @return \Drupal\Core\Database\StatementInterface|null
   *   Results from search query execute() method, or NULL if the search
   *   failed.
   */
  protected function findResults() {
    $keys = $this->keywords;

    // Build matching conditions.
    $query = $this->database
      ->select('search_index', 'i', ['target' => 'replica'])
      ->extend('Drupal\search\SearchQuery')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->join('api_doc_field_data', 'api_doc', 'api_doc.id = i.sid AND api_doc.langcode = i.langcode');
    // TODO: Is there anything which should be implemented in connection with this?
    $query->addTag('api_doc_access')
      ->searchExpression($keys, $this->getPluginId());

    // Handle advanced search filters in the f query string.
    // \Drupal::request()->query->get('f') is an array that looks like this in
    // the URL: ?f[]=type:page&f[]=term:27&f[]=term:13&f[]=langcode:en
    // So $parameters['f'] looks like:
    // array('type:page', 'term:27', 'term:13', 'langcode:en');
    // We need to parse this out into query conditions, some of which go into
    // the keywords string, and some of which are separate conditions.
    $parameters = $this->getParameters();
    if (!empty($parameters['f']) && is_array($parameters['f'])) {
      $filters = [];
      // Match any query value that is an expected option and a value
      // separated by ':' like 'term:27'.
      $pattern = '/^(' . implode('|', array_keys($this->advanced)) . '):([^ ]*)/i';
      foreach ($parameters['f'] as $item) {
        if (preg_match($pattern, $item, $m)) {
          // Use the matched value as the array key to eliminate duplicates.
          $filters[$m[1]][$m[2]] = $m[2];
        }
      }

      // Now turn these into query conditions. This assumes that everything in
      // $filters is a known type of advanced search.
      foreach ($filters as $option => $matched) {
        $info = $this->advanced[$option];
        // Insert additional conditions. By default, all use the OR operator.
        $operator = empty($info['operator']) ? 'OR' : $info['operator'];
        $where = new Condition($operator);
        foreach ($matched as $value) {
          $where->condition($info['column'], $value);
        }
        $query->condition($where);
        if (!empty($info['join'])) {
          $query->join($info['join']['table'], $info['join']['alias'], $info['join']['condition']);
        }
      }
    }

    // Run the query.
    $find = $query
      // Add the language code of the indexed item to the result of the query,
      // since the API Documentation will be rendered using the respective language.
      ->fields('i', ['langcode'])
      // And since SearchQuery makes these into GROUP BY queries, if we add
      // a field, for PostgreSQL we also need to make it an aggregate or a
      // GROUP BY. In this case, we want GROUP BY.
      ->groupBy('i.langcode')
      ->limit(10)
      ->execute();

    // Check query status and set messages if needed.
    $status = $query->getStatus();

    if ($status & SearchQuery::EXPRESSIONS_IGNORED) {
      drupal_set_message($this->t('Your search used too many AND/OR expressions. Only the first @count terms were included in this search.', ['@count' => $this->searchSettings->get('and_or_limit')]), 'warning');
    }

    if ($status & SearchQuery::LOWER_CASE_OR) {
      drupal_set_message($this->t('Search for either of the two terms with uppercase <strong>OR</strong>. For example, <strong>cats OR dogs</strong>.'), 'warning');
    }

    if ($status & SearchQuery::NO_POSITIVE_KEYWORDS) {
      drupal_set_message($this->formatPlural($this->searchSettings->get('index.minimum_word_size'), 'You must include at least one keyword to match in the content, and punctuation is ignored.', 'You must include at least one keyword to match in the content. Keywords must be at least @count characters, and punctuation is ignored.'), 'warning');
    }

    return $find;
  }

  /**
   * Prepares search results for rendering.
   *
   * @param \Drupal\Core\Database\StatementInterface $found
   *   Results found from a successful search query execute() method.
   *
   * @return array
   *   Array of search result item render arrays (empty array if no results).
   */
  protected function prepareResults(StatementInterface $found) {
    $results = [];

    $api_doc_storage = $this->entityManager->getStorage('api_doc');
    $api_doc_render = $this->entityManager->getViewBuilder('api_doc');
    $keys = $this->keywords;

    foreach ($found as $item) {
      // Render the API Documentation.
      /** @var \Drupal\devportal_api_entities\APIDocInterface $api_doc */
      $api_doc = $api_doc_storage->load($item->sid)->getTranslation($item->langcode);
      $build = $api_doc_render->view($api_doc, 'search_result', $item->langcode);

      /** @var \Drupal\devportal_api_entities\APIDocTypeInterface $type*/
      $type = $this->entityManager->getStorage('api_doc_type')->load($api_doc->bundle());

      unset($build['#theme']);
      $build['#pre_render'][] = [$this, 'removeSubmittedInfo'];

      // Fetch comments for snippet.
      $rendered = $this->renderer->renderPlain($build);
      $this->addCacheableDependency(CacheableMetadata::createFromRenderArray($build));
      // TODO: What is this?
      //$rendered .= ' ' . $this->moduleHandler->invoke('comment', 'node_update_index', [$node]);

      $extra = $this->moduleHandler->invokeAll('api_doc_search_result', [$api_doc]);

      $language = $this->languageManager->getLanguage($item->langcode);
      // TODO: Owner is not always available at custom entity types!
      $username = [
        '#theme' => 'username',
        '#account' => $api_doc->getOwner(),
      ];

      $result = [
        'link' => $api_doc->url('canonical', ['absolute' => TRUE, 'language' => $language]),
        'type' => $type->label(),
        'title' => $api_doc->label(),
        // TODO: Original line was: 'node' => $node,
        'api_doc' => $api_doc,
        'extra' => $extra,
        'score' => $item->calculated_score,
        'snippet' => search_excerpt($keys, $rendered, $item->langcode),
        'langcode' => $api_doc->language()->getId(),
      ];

      $this->addCacheableDependency($api_doc);

      // TODO: Owner is not always available at custom entity types.
      // We have to separately add the API Documentation owner's cache tags
      // because search module doesn't use the rendering system, it does its own
      // rendering without taking cacheability metadata into account. So we have
      // to do it explicitly here.
      $this->addCacheableDependency($api_doc->getOwner());

      // TODO: Is this will be needed for us? If so, it needs to be implemented.
      /*if ($type->displaySubmitted()) {
        $result += [
          'user' => $this->renderer->renderPlain($username),
          'date' => $api_doc->getChangedTime(),
        ];
      }*/

      $results[] = $result;

    }
    return $results;
  }

  /**
   * Removes the submitted by information from the build array.
   *
   * This information is being removed from the rendered API Documentation that
   * is used to build the search result snippet. It just doesn't make sense to
   * have it displayed in the snippet.
   *
   * @param array $build
   *   The build array.
   *
   * @return array
   *   The modified build array.
   */
  public function removeSubmittedInfo(array $build) {
    // TODO: There is no created field at API Doc entity type.
    //unset($build['created']);
    unset($build['uid']);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function updateIndex() {
    // Interpret the cron limit setting as the maximum number of
    // API Documentations to index per cron run.
    $limit = (int) $this->searchSettings->get('index.cron_limit');

    $query = db_select('api_doc', 'api_doc', ['target' => 'replica']);
    $query->addField('api_doc', 'id');
    $query->leftJoin('search_dataset', 'sd', 'sd.sid = api_doc.id AND sd.type = :type', [':type' => $this->getPluginId()]);
    $query->addExpression('CASE MAX(sd.reindex) WHEN NULL THEN 0 ELSE 1 END', 'ex');
    $query->addExpression('MAX(sd.reindex)', 'ex2');
    $query->condition(
        $query->orConditionGroup()
          ->where('sd.sid IS NULL')
          ->condition('sd.reindex', 0, '<>')
      );
    $query->orderBy('ex', 'DESC')
      ->orderBy('ex2')
      ->orderBy('api_doc.id')
      ->groupBy('api_doc.id')
      ->range(0, $limit);

    $api_doc_ids = $query->execute()->fetchCol();
    if (!$api_doc_ids) {
      return;
    }

    $api_doc_storage = $this->entityManager->getStorage('api_doc');
    foreach ($api_doc_storage->loadMultiple($api_doc_ids) as $api_doc) {
      $this->indexAPIDoc($api_doc);
    }
  }

  /**
   * Indexes a single API Documentation.
   *
   * @param \Drupal\devportal_api_entities\APIDocInterface $api_doc
   *   The API Documentation to index.
   */
  protected function indexAPIDoc(APIDocInterface $api_doc) {
    $languages = $api_doc->getTranslationLanguages();
    $api_doc_render = $this->entityManager->getViewBuilder('api_doc');

    foreach ($languages as $language) {
      $api_doc = $api_doc->getTranslation($language->getId());
      // Render the API Documentation.
      $build = $api_doc_render->view($api_doc, 'search_index', $language->getId());

      unset($build['#theme']);

      // Add the title to text so it is searchable.
      $build['search_title'] = [
        '#prefix' => '<h1>',
        '#plain_text' => $api_doc->label(),
        '#suffix' => '</h1>',
        '#weight' => -1000
      ];
      $text = $this->renderer->renderPlain($build);

      // Fetch extra data normally not visible.
      $extra = $this->moduleHandler->invokeAll('api_doc_update_index', [$api_doc]);
      foreach ($extra as $t) {
        $text .= $t;
      }

      // Update index, using search index "type" equal to the plugin ID.
      search_index($this->getPluginId(), $api_doc->id(), $language->getId(), $text);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function indexClear() {
    // All APIDocSearch pages share a common search index "type" equal to
    // the plugin ID.
    search_index_clear($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  public function markForReindex() {
    // All APIDocSearch pages share a common search index "type" equal to
    // the plugin ID.
    search_mark_for_reindex($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  public function indexStatus() {
    $total = $this->database->select('api_doc')
      ->countQuery()
      ->execute()
      ->fetchField();

    // SELECT COUNT(DISTINCT api_doc.id)
    // FROM {api_doc} api_doc
    // LEFT JOIN {search_dataset} sd ON sd.sid = api_doc.id AND sd.type = :type
    // WHERE sd.sid IS NULL OR sd.reindex <> 0
    $query = $this->database->select('api_doc', 'api_doc');
    $sd = $query->leftJoin('search_dataset', 'sd', '%alias.sid = api_doc.id AND sd.type = :type', [':type' => $this->getPluginId()]);
    $or = $query->orConditionGroup();
    $or->isNull($sd . '.sid');
    $or->condition($sd . '.reindex', 0, '<>');
    $query->condition($or);
    $remaining = $query->distinct()
      ->countQuery()
      ->execute()
      ->fetchField();

    return ['remaining' => $remaining, 'total' => $total];
  }

  /**
   * {@inheritdoc}
   */
  public function searchFormAlter(array &$form, FormStateInterface $form_state) {
    $parameters = $this->getParameters();
    $keys = $this->getKeywords();
    $used_advanced = !empty($parameters[self::ADVANCED_FORM]);
    if ($used_advanced) {
      $f = isset($parameters['f']) ? (array) $parameters['f'] : [];
      $defaults = $this->parseAdvancedDefaults($f, $keys);
    }
    else {
      $defaults = ['keys' => $keys];
    }

    $form['basic']['keys']['#default_value'] = $defaults['keys'];

    // Add advanced search keyword-related boxes.
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced search'),
      '#attributes' => ['class' => ['search-advanced']],
      '#access' => $this->account && $this->account->hasPermission('use advanced search'),
      '#open' => $used_advanced,
    ];
    $form['advanced']['keywords-fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Keywords'),
    ];

    $form['advanced']['keywords'] = [
      '#prefix' => '<div class="criterion">',
      '#suffix' => '</div>',
    ];

    $form['advanced']['keywords-fieldset']['keywords']['or'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Containing any of the words'),
      '#size' => 30,
      '#maxlength' => 255,
      '#default_value' => isset($defaults['or']) ? $defaults['or'] : '',
    ];

    $form['advanced']['keywords-fieldset']['keywords']['phrase'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Containing the phrase'),
      '#size' => 30,
      '#maxlength' => 255,
      '#default_value' => isset($defaults['phrase']) ? $defaults['phrase'] : '',
    ];

    $form['advanced']['keywords-fieldset']['keywords']['negative'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Containing none of the words'),
      '#size' => 30,
      '#maxlength' => 255,
      '#default_value' => isset($defaults['negative']) ? $defaults['negative'] : '',
    ];

    // Add API Documentation types.
    $types = array_map(['\Drupal\Component\Utility\Html', 'escape'], devportal_api_entities_api_doc_type_get_names());
    $form['advanced']['types-fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Types'),
    ];
    $form['advanced']['types-fieldset']['type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Only of the type(s)'),
      '#prefix' => '<div class="criterion">',
      '#suffix' => '</div>',
      '#options' => $types,
      '#default_value' => isset($defaults['type']) ? $defaults['type'] : [],
    ];

    $form['advanced']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Advanced search'),
      '#prefix' => '<div class="action">',
      '#suffix' => '</div>',
      '#weight' => 100,
    ];

    // Add languages.
    $language_options = [];
    $language_list = $this->languageManager->getLanguages(LanguageInterface::STATE_ALL);
    foreach ($language_list as $langcode => $language) {
      // Make locked languages appear special in the list.
      $language_options[$langcode] = $language->isLocked() ? $this->t('- @name -', ['@name' => $language->getName()]) : $language->getName();
    }
    if (count($language_options) > 1) {
      $form['advanced']['lang-fieldset'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Languages'),
      ];
      $form['advanced']['lang-fieldset']['language'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Languages'),
        '#prefix' => '<div class="criterion">',
        '#suffix' => '</div>',
        '#options' => $language_options,
        '#default_value' => isset($defaults['language']) ? $defaults['language'] : [],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildSearchUrlQuery(FormStateInterface $form_state) {
    // Read keyword and advanced search information from the form values,
    // and put these into the GET parameters.
    $keys = trim($form_state->getValue('keys'));
    $advanced = FALSE;

    // Collect extra filters.
    $filters = [];
    if ($form_state->hasValue('type') && is_array($form_state->getValue('type'))) {
      // Retrieve selected types - Form API sets the value of unselected
      // checkboxes to 0.
      foreach ($form_state->getValue('type') as $type) {
        if ($type) {
          $advanced = TRUE;
          $filters[] = 'type:' . $type;
        }
      }
    }

    if ($form_state->hasValue('language') && is_array($form_state->getValue('language'))) {
      foreach ($form_state->getValue('language') as $language) {
        if ($language) {
          $advanced = TRUE;
          $filters[] = 'language:' . $language;
        }
      }
    }
    if ($form_state->getValue('or') != '') {
      if (preg_match_all('/ ("[^"]+"|[^" ]+)/i', ' ' . $form_state->getValue('or'), $matches)) {
        $keys .= ' ' . implode(' OR ', $matches[1]);
        $advanced = TRUE;
      }
    }
    if ($form_state->getValue('negative') != '') {
      if (preg_match_all('/ ("[^"]+"|[^" ]+)/i', ' ' . $form_state->getValue('negative'), $matches)) {
        $keys .= ' -' . implode(' -', $matches[1]);
        $advanced = TRUE;
      }
    }
    if ($form_state->getValue('phrase') != '') {
      $keys .= ' "' . str_replace('"', ' ', $form_state->getValue('phrase')) . '"';
      $advanced = TRUE;
    }
    $keys = trim($keys);

    // Put the keywords and advanced parameters into GET parameters. Make sure
    // to put keywords into the query even if it is empty, because the page
    // controller uses that to decide it's time to check for search results.
    $query = ['keys' => $keys];
    if ($filters) {
      $query['f'] = $filters;
    }
    // Record that the person used the advanced search form, if they did.
    if ($advanced) {
      $query[self::ADVANCED_FORM] = '1';
    }

    return $query;
  }

  /**
   * Parses the advanced search form default values.
   *
   * @param array $f
   *   The 'f' query parameter set up in self::buildUrlSearchQuery(), which
   *   contains the advanced query values.
   * @param string $keys
   *   The search keywords string, which contains some information from the
   *   advanced search form.
   *
   * @return array
   *   Array of default form values for the advanced search form, including
   *   a modified 'keys' element for the bare search keywords.
   */
  protected function parseAdvancedDefaults($f, $keys) {
    $defaults = [];

    // Split out the advanced search parameters.
    foreach ($f as $advanced) {
      list($key, $value) = explode(':', $advanced, 2);
      if (!isset($defaults[$key])) {
        $defaults[$key] = [];
      }
      $defaults[$key][] = $value;
    }

    // Split out the negative, phrase, and OR parts of keywords.

    // For phrases, the form only supports one phrase.
    $matches = [];
    $keys = ' ' . $keys . ' ';
    if (preg_match('/ "([^"]+)" /', $keys, $matches)) {
      $keys = str_replace($matches[0], ' ', $keys);
      $defaults['phrase'] = $matches[1];
    }

    // Negative keywords: pull all of them out.
    if (preg_match_all('/ -([^ ]+)/', $keys, $matches)) {
      $keys = str_replace($matches[0], ' ', $keys);
      $defaults['negative'] = implode(' ', $matches[1]);
    }

    // OR keywords: pull up to one set of them out of the query.
    if (preg_match('/ [^ ]+( OR [^ ]+)+ /', $keys, $matches)) {
      $keys = str_replace($matches[0], ' ', $keys);
      $words = explode(' OR ', trim($matches[0]));
      $defaults['or'] = implode(' ', $words);
    }

    // Put remaining keywords string back into keywords.
    $defaults['keys'] = trim($keys);

    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // There are no custom configuration options, return with the base form.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

}
