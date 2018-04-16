<?php

namespace Drupal\devportal_api_entities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Defines a validation constraint for API Meta Parameters.
 *
 * @Constraint(
 *   id = "APIMetaParam",
 *   label = @Translation("API Meta Parameter", context = "Validation"),
 * )
 */
class APIMetaParamConstraint extends Constraint {

  public $message = "Only that reference must be set that is denoted by the In field.";

}
