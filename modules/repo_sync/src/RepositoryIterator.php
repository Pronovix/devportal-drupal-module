<?php

namespace Drupal\devportal_repo_sync;

/**
 * Iterator for the repository content.
 *
 * It loads file contents for the paths in a given repository.
 */
class RepositoryIterator extends \ArrayIterator implements \Iterator, \Countable {

  /**
   * Remote file cache.
   *
   * @var array
   */
  protected $cache;

  /**
   * Callback function to download the file.
   *
   * @var callable
   */
  protected $filegetter;

  /**
   * Repository identifier.
   *
   * @var string
   */
  protected $repository;

  /**
   * Branch or tag.
   *
   * @var string
   */
  protected $version;

  /**
   * Revision map.
   *
   * Key is the filename, value is the commit hash.
   *
   * @var array
   */
  protected $revisions;

  /**
   * RepositoryIterator constructor.
   *
   * @param string $repository
   *   Repository identifier.
   * @param string $version
   *   Branch or tag.
   * @param array $files
   *   List of files.
   * @param array $revisions
   *   Map of file revisions.
   * @param callable $filegetter
   *   A function that downloads the remote file.
   * @param int $flags
   *   See \ArrayIterator::__construct().
   */
  public function __construct($repository, $version, array $files, array $revisions, callable $filegetter, $flags = 0) {
    parent::__construct($files, $flags);
    $this->repository = $repository;
    $this->version = $version;
    $this->filegetter = $filegetter;
    $this->revisions = $revisions;
    $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function current() {
    $file = parent::current();

    return [
      'repository' => $this->repository,
      'filename' => $file,
      'version' => $this->version,
      'content' => $this->getContent($file),
      'commit' => $this->revisions[$file],
    ];
  }

  /**
   * Loads the remote content.
   *
   * @param string $file
   *   Filename.
   *
   * @return mixed
   *   File content.
   */
  protected function getContent($file) {
    if (empty($this->cache[$file])) {
      $getter = $this->filegetter;
      $this->cache[$file] = $getter($file);
    }

    return $this->cache[$file];
  }

  /**
   * Resets the internal cache.
   *
   * @param string|null $key
   */
  public function reset($key = NULL) {
    if ($key === NULL) {
      $this->cache = [];
    }
    else {
      unset($this->cache[$key]);
    }
  }

}
