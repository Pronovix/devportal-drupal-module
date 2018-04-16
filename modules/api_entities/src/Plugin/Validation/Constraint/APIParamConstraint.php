<?php

namespace Drupal\devportal_api_entities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Defines a validation constraint for API Parameters.
 *
 * @Constraint(
 *   id = "APIParam",
 *   label = @Translation("API Parameter", context = "Validation"),
 * )
 */
class APIParamConstraint extends Constraint {

  public $message = "Either a Global parameter or a Meta parameter must be referenced, but not both.";

}
