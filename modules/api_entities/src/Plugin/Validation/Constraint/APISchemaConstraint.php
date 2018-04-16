<?php

namespace Drupal\devportal_api_entities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Defines a validation constraint for API Schemas.
 *
 * @Constraint(
 *   id = "APISchema",
 *   label = @Translation("API Schema", context = "Validation"),
 * )
 */
class APISchemaConstraint extends Constraint {

  public $message = "Either a Global schema reference or an Inline schema definition must be present, but not both.";

}
