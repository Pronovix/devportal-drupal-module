<?php

/**
 * @file
 * Hooks provided by the Devportal API Reference module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Returns the array of fields of the API Reference content type.
 *
 * Takes the values of the fields from the submitted file and returns the
 * associative array of the fields and the fields' values of the API Reference
 * content type.
 *
 * @param \Drupal\devportal_api_reference\ReferenceInterface $type
 *   The handler object of the API reference type.
 * @param object $doc
 *   The raw structure.
 * @param \Drupal\file\FileInterface $file
 *   The uploaded file.
 * @param \Symfony\Component\HttpFoundation\Request|null $request
 *   (optional) The HTTP request.
 *
 * @return array
 *   The associative array of the fields and the fields' values of the API
 *   Reference content type.
 */
function hook_devportal_api_reference_fields(\Drupal\devportal_api_reference\ReferenceInterface $type, $doc, \Drupal\file\FileInterface $file, ?\Symfony\Component\HttpFoundation\Request $request = NULL) {
  $description = (string) $type->getDescription($doc);
  return [
    'title' => [['value' => (string) $type->getTitle($doc)]],
    'field_version' => [['value' => (string) $type->getVersion($doc)]],
    'field_description' => [
      [
        'value' => $description,
        'summary' => $description ? text_summary($description, 'github_flavored_markdown') : '',
        'format' => 'github_flavored_markdown',
      ],
    ],
    'field_tags' => array_filter(array_map(function (stdClass $tag): array {
      $term = devportal_api_reference_ensure_term('tags', $tag->name, $tag->description);
      return $term ? ['target_id' => $term->id()] : [];
    }, $doc->tags)),
  ];
}

/**
 * @} End of "addtogroup hooks".
 */
