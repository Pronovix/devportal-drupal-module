<?php

namespace Drupal\devportal_repo_sync;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\Entity\Node;

/**
 * FakeBookNode represets a fake node, that is passed to the bookmanager.
 *
 * The point of this class is to prevent the book manager to accidentally save
 * the fake node that is being passed to it.
 */
final class FakeBookNode extends Node {

  /**
   * Makes the node save fail.
   *
   * Normally, this function is never called.
   *
   * @throws \Exception
   */
  private static function nope() {
    throw new \Exception('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    static::nope();
  }

  /**
   * {@inheritdoc}
   * @throws \Exception
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    static::nope();
  }

  /**
   * {@inheritdoc}
   * @throws \Exception
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    static::nope();
  }

}
