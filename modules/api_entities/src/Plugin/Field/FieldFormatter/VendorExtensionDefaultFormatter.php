<?php

namespace Drupal\devportal_api_entities\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'vendor_extension_default' formatter.
 *
 * @FieldFormatter(
 *   id = "vendor_extension_default",
 *   label = @Translation("Vendor extension"),
 *   field_types = {
 *     "vendor_extension"
 *   }
 * )
 */
class VendorExtensionDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        // @TODO Theme function.
        '#markup' => $this->t('Name: @name Value: @value', [
          '@name' => $item->name,
          '@value' => $item->value,
        ]),
      ];
    }

    return $element;
  }

}
