<?php

namespace Drupal\devportal_repo_sync\Plugin\FileType;

use DOMDocument;
use DOMXPath;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Psr\Http\Message\StreamInterface;

/**
 * @FileType(
 *   id = "html",
 *   label = "HTML",
 *   matcher = "#\.(html|md|markdown)$#",
 *   weight = 0
 * )
 */
class HTML extends FileTypeBase {

  /**
   * {@inheritdoc}
   */
  public function import(?EntityInterface $entity, string $filename, string $prefix, StreamInterface $content): ?EntityInterface {
    if ($entity === NULL) {
      /** @var \Drupal\node\NodeInterface $entity */
      $entity = Node::create([
        'type' => 'documentation',
      ]);
      $entity->setOwnerId($this->currentUser->id());
      $entity->set('path', rtrim($prefix, '/') . '/' . $filename);
    }
    $content = (string) $content;

    $title = $this->getTitle($content) ?: $filename;
    $entity->setTitle($title);

    $entity->set('field_content', [
      'value' => $content,
      'format' => 'full_html',
    ]);

    return $entity;
  }

  /**
   * Tries to extract a title from a HTML fragment.
   *
   * @param string $content
   *   HTML content.
   *
   * @return string
   *   Title if found, empty string otherwise.
   */
  protected function getTitle(string $content): string {
    $doc = new DOMDocument();
    $doc->loadHTML($content);

    $xpath = new DOMXPath($doc);
    $headers = $xpath->query('//h1');

    foreach ($headers as $header) {
      /** @var \DOMElement $header */
      return trim($header->textContent);
    }

    return "";
  }

}
