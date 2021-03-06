<?php

namespace Drupal\devportal_api_reference\Plugin;

use Drupal\devportal_api_reference\Exception\RuntimeException;

/**
 * Exception for OpenApi validation errors.
 */
class OpenApiValidationException extends RuntimeException {

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
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * Sets the list of stored errors.
   *
   * @param array $errors
   *   Array of errors.
   *
   * @return self
   *   New self.
   */
  public function setErrors(array $errors): self {
    $this->errors = $errors;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($message = '', $code = 0, \Throwable $previous = NULL, array $errors = []) {
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
   * @return self
   *   New OpenApiValidationException.
   */
  public static function fromErrors(array $errors, \Throwable $previous = NULL): self {
    return new self(implode(PHP_EOL, array_map(static function ($error) {
      $msg = '';

      if ($error['property']) {
        $msg .= " [{$error['property']}]";
      }

      $msg .= " {$error['message']}";

      return $msg;
    }, $errors)), 0, $previous, $errors);
  }

}
