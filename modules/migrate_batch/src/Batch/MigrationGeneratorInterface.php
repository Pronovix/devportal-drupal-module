<?php

namespace Drupal\devportal_migrate_batch\Batch;

/**
 * Objects that generate directly or indirectly migrations.
 */
interface MigrationGeneratorInterface {

  /**
   * Returns the id of this generator.
   *
   * @return string|int|null
   *   Id.
   */
  public function id();

  /**
   * Loads an instance.
   *
   * @param string|int $id
   *   Internal id of the generator.
   *
   * @return static
   */
  public static function load($id);

  /**
   * A regexp pattern matching the migration ids originating from this class.
   *
   * @return string
   *   Regexp pattern.
   */
  public function getFilterPattern();

  /**
   * Removes local content that is deleted from the source.
   *
   * @param bool $all
   *   If set to true, it will check all content to make sure that the state of
   *   the local data are consistent with the source. Else it will check
   *   the recent changes and looks for deleted content there.
   */
  public function cleanUp($all = FALSE);

  /**
   * List of callbacks to execute after the migration is finished.
   *
   * @return array
   *   List of callbacks.
   */
  public function getMigrationAfterCallbacks();

  /**
   * A list of generators that should be run after the import.
   *
   * This is useful when a generator gets imported.
   *
   * @return \Drupal\devportal_migrate_batch\Batch\MigrationGeneratorInterface[]
   *   List of generators.
   */
  public function relatedGenerators();

}
