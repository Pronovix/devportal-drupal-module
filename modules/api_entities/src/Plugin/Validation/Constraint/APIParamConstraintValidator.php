<?php

namespace Drupal\devportal_api_entities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the APIParam constraint.
 */
class APIParamConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\devportal_api_entities\Entity\APIParam $value */
    $global = $value->get('api_global_param')->isEmpty();
    $meta = $value->get('api_meta_param')->isEmpty();
    if (!($global XOR $meta)) {
      $this->context->addViolation($constraint->message);
    }
  }

}
