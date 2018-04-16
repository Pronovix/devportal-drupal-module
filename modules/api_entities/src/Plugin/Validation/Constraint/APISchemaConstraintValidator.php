<?php

namespace Drupal\devportal_api_entities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the APISchema constraint.
 */
class APISchemaConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\devportal_api_entities\Entity\APISchema $value */
    $global = $value->get('api_global_schema')->isEmpty();
    $inline = $value->get('inline_schema_def')->isEmpty();
    if (!($global XOR $inline)) {
      $this->context->addViolation($constraint->message);
    }
  }

}
