<?php

namespace Drupal\devportal_api_entities;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the API Doc entity type.
 */
class APIDocViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Change the relationship label/title to a bit more meaningful one
    // (original was "Taxonomy term" for both Produces and Consumes fields).
    $data['api_doc__produces']['produces_target_id']['relationship']['label'] = $this->t('Produces taxonomy term');
    $data['api_doc__produces']['produces_target_id']['relationship']['title'] = $this->t('Produces taxonomy term');
    $data['api_doc__consumes']['consumes_target_id']['relationship']['label'] = $this->t('Consumes taxonomy term');
    $data['api_doc__consumes']['consumes_target_id']['relationship']['title'] = $this->t('Consumes taxonomy term');

    $data['api_doc']['api_doc_bulk_form'] = [
      'title' => $this->t('API Documentation operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple API Documentations.'),
      'field' => [
        'id' => 'api_doc_bulk_form',
      ],
    ];

    // Add search table, fields, filters, etc., but only if a page using the
    // api_doc_search plugin is enabled.
    if ($this->moduleHandler->moduleExists('search')) {
      $enabled = FALSE;
      // This would mean a hard dependency on search.module with proper
      // dependency injection.
      // @TODO Discuss if core search is needed, or should we stick to this.
      $search_page_repository = \Drupal::service('search.search_page_repository');
      foreach ($search_page_repository->getActiveSearchpages() as $page) {
        if ($page->getPlugin()->getPluginId() == 'api_doc_search') {
          $enabled = TRUE;
          break;
        }
      }

      if ($enabled) {
        $data['api_doc_search_index']['table']['group'] = $this->t('Search');

        // Automatically join to the api_doc table (or actually,
        // api_doc_field_data). Use a Views table alias to allow other modules
        // to use this table too, if they use the search index.
        $data['api_doc_search_index']['table']['join'] = [
          'api_doc_field_data' => [
            'left_field' => 'id',
            'field' => 'sid',
            'table' => 'search_index',
            'extra' => "api_doc_search_index.type = 'api_doc_search' AND api_doc_search_index.langcode = api_doc_field_data.langcode",
          ]
        ];

        $data['api_doc_search_total']['table']['join'] = [
          'api_doc_search_index' => [
            'left_field' => 'word',
            'field' => 'word',
          ],
        ];

        $data['api_doc_search_dataset']['table']['join'] = [
          'api_doc_field_data' => [
            'left_field' => 'sid',
            'left_table' => 'api_doc_search_index',
            'field' => 'sid',
            'table' => 'search_dataset',
            'extra' => 'api_doc_search_index.type = api_doc_search_dataset.type AND api_doc_search_index.langcode = api_doc_search_dataset.langcode',
            'type' => 'INNER',
          ],
        ];

        $data['api_doc_search_index']['score'] = [
          'title' => $this->t('Score'),
          'help' => $this->t('The score of the search item. This will not be used if the search filter is not also present.'),
          'field' => [
            'id' => 'search_score',
            'float' => TRUE,
            'no group by' => TRUE,
          ],
          'sort' => [
            'id' => 'search_score',
            'no group by' => TRUE,
          ],
        ];

        $data['api_doc_search_index']['keys'] = [
          'title' => $this->t('Search Keywords'),
          'help' => $this->t('The keywords to search for.'),
          'filter' => [
            'id' => 'search_keywords',
            'no group by' => TRUE,
            'search_type' => 'api_doc_search',
          ],
          'argument' => [
            'id' => 'search',
            'no group by' => TRUE,
            'search_type' => 'api_doc_search',
          ],
        ];

      }
    }

    return $data;
  }

}
