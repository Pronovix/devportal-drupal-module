<?php

namespace Drupal\devportal_api_entities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the APIMetaParam constraint.
 */
class APIMetaParamConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\devportal_api_entities\Entity\APIMetaParam $value */
    $fields = [
      'path' => 'api_path_param',
      'body' => 'api_body_param',
      'query' => 'api_query_param',
      'header' => 'api_header_param',
      'form_data' => 'api_form_data_param',
    ];
    // Fetch the In value from the entity.
    $in = $value->get('param_in')->value;
    // Cycle through all five ER fields.
    foreach ($fields as $in_value => $field_name) {
      if ($in == $in_value) {
        // The ER field denoted by the In value MUST NOT be empty.
        if ($value->get($field_name)->isEmpty()) {
          $this->context->addViolation($constraint->message);
        }
      }
      else {
        // All the other ER fields MUST be empty.
        if (!$value->get($field_name)->isEmpty()) {
          $this->context->addViolation($constraint->message);
        }
      }
    }
  }

}
