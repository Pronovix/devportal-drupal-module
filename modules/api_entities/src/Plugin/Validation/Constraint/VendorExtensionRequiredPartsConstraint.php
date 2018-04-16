<?php

namespace Drupal\devportal_api_entities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Defines a required parts validation constraint for vendor extensions.
 *
 * @Constraint(
 *   id = "VendorExtensionRequiredParts",
 *   label = @Translation("Required vendor extension parts", context = "Validation"),
 * )
 */
class VendorExtensionRequiredPartsConstraint extends Constraint {

  public $message = "Both the name and the value are required.";

}
