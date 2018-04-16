<?php

namespace Drupal\devportal_api_entities\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'vendor_extension_default' widget.
 *
 * @FieldWidget(
 *   id = "vendor_extension_default",
 *   label = @Translation("Vendor extension"),
 *   field_types = {
 *     "vendor_extension",
 *   }
 * )
 */
class VendorExtensionDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // @FIXME Provide an element type for this, to allow proper overrides.
    $element['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#default_value' => isset($items[$delta]->name) ? $items[$delta]->name : NULL,
      '#required' => $element['#required'],
    ];
    $element['value'] = [
      '#type' => 'textarea',
      '#title' => t('Value'),
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#required' => $element['#required'],
    ];
    $element['#type'] = 'fieldset';
    // We could set this form element to be required here, but:
    // - Sitebuilders would have to provide a default value (which is not the
    //   case for fields that _they_ made required).
    // - This approach would make the Field UI's 'Required' checkbox useless (or
    //   even misleading), as the field itself would be required as well.
//    $element['name']['#required'] = TRUE;

    // @TODO Don't allow submitting only a value (without a name).

    return $element;
  }

}
