<?php

namespace Drupal\devportal_api_reference\Plugin;

/**
 * Exception for OpenApi validation errors.
 */
class OpenApiValidationException extends \Exception {

  /**
   * List of errors.
   *
   * @var array
   */
  protected $errors = [];

  /**
   * Returns the list of stored errors.
   *
   * @return array
   *   Array of validation errors.
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * Sets the list of stored errors.
   *
   * @param array $errors
   *   Array of errors.
   *
   * @return \self
   *   New self.
   */
  public function setErrors(array $errors) {
    $this->errors = $errors;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($message = "", $code = 0, \Throwable $previous = NULL, array $errors = []) {
    parent::__construct($message, $code, $previous);
    $this->errors = $errors;
  }

  /**
   * Factory method that creates an instance from a list of validation errors.
   *
   * @param array $errors
   *   Array of validation errors.
   * @param \Throwable|null $previous
   *   The previous exception or NULL.
   *
   * @return \self
   *   New OpenApiValidationException.
   */
  public static function fromErrors(array $errors, \Throwable $previous = NULL) {
    return new self(implode(PHP_EOL, array_map(function ($error) {
      $msg = '';

      if ($error['property']) {
        $msg .= " [{$error['property']}]";
      }

      $msg .= " {$error['message']}";

      return $msg;
    }, $errors)), 0, $previous, $errors);
  }

}
