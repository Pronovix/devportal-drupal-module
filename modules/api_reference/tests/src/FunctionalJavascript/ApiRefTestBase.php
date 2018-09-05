<?php

namespace Drupal\Tests\devportal_api_reference\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\DrupalSelenium2Driver;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\file\Functional\FileFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Base class for API Reference tests.
 */
abstract class ApiRefTestBase extends JavascriptTestBase {

  use FileFieldCreationTrait;
  use TestFileCreationTrait;

  protected const TITLE_NAME = 'title[0][value]';
  protected const VERSION_NAME = 'field_version[0][value]';
  protected const FILEFIELD_NAME = 'files[field_source_file_0]';


  protected static $modules = [
    'devportal_api_reference',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct($name = NULL, array $data = [], string $dataName = '') {
    $this->minkDefaultDriverClass = DrupalSelenium2Driver::class;
    parent::__construct($name, $data, $dataName);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('page_title_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * {@inheritdoc}
   */
  public function createScreenshot($filename_prefix = '', $set_background_color = TRUE) {
    $log_dir = getenv('BROWSERTEST_OUTPUT_DIRECTORY') ?: $this->container
      ->get('file_system')
      ->realpath('public://');

    $screenshots_dir = "{$log_dir}/screenshots";
    if (!is_dir($screenshots_dir)) {
      mkdir($screenshots_dir, 0777, TRUE);
    }

    /** @var \Drupal\Core\Database\Connection $database */
    $database = $this->container->get('database');
    $test_id = str_replace('test', '', $database->tablePrefix());

    $filename = file_create_filename("{$filename_prefix}-{$test_id}.png", $screenshots_dir);
    $this->container
      ->get('logger.factory')
      ->get('devportal')
      ->debug("Creating new screenshot: {$filename}.");
    parent::createScreenshot($filename, $set_background_color);
  }

  /**
   * Uploads a file on the node edit form.
   *
   * @param string $filename
   *   Name of the file relative to the fixture directory.
   */
  protected function uploadFile(string $filename) {
    $this
      ->getSession()
      ->getPage()
      ->attachFileToField(static::FILEFIELD_NAME, $this->getFixture($filename));

    $this
      ->assertSession()
      ->waitForLink($filename);
  }

  /**
   * Clicks on the submit button.
   */
  protected function clickSubmit() {
    $this->click('input[name="op"][value="Save"]');
  }

  /**
   * Selects the 'manual' mode on the api_reference node form.
   */
  protected function selectManualMode() {
    $this->selectMode('manual', ['id_or_name', static::TITLE_NAME]);
  }

  /**
   * Selects the 'upload' mode on the api_reference node form.
   */
  protected function selectUploadMode() {
    $this->selectMode('upload', ['id_or_name', static::FILEFIELD_NAME]);
  }

  /**
   * Selects a mode on the api_reference node form.
   *
   * @param string $mode
   *   The mode to select.
   * @param array $locator
   *   An element locator to verify that the mode switch AJAX is finished and
   *   successful.
   */
  protected function selectMode(string $mode, array $locator) {
    $this
      ->getSession()
      ->getPage()
      ->find('css', "input[name=\"mode_selector\"][value=\"{$mode}\"]")
      ->click();

    $this
      ->assertSession()
      ->waitForElement('named', $locator);
  }

  /**
   * Returns the absolute path to a fixture.
   *
   * @param string $filename
   *   File name inside the fixtures directory.
   * @param string $module
   *   Name of the module where the fixture is.
   *
   * @return string
   *   Path.
   */
  protected function getFixture(string $filename, string $module = 'devportal_api_reference'): string {
    return DRUPAL_ROOT . '/' . drupal_get_path('module', $module) . "/tests/fixtures/{$filename}";
  }

}
