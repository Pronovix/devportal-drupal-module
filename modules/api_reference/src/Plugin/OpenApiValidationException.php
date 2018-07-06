<?php

namespace Drupal\devportal_api_reference\Plugin;

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
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * Sets the list of stored errors.
   *
   * @param array $errors
   *
   * @return OpenApiValidationException
   */
  public function setErrors($errors) {
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
   * @param \Throwable|NULL $previous
   *
   * @return static
   */
  public static function fromErrors(array $errors, \Throwable $previous = NULL) {
    return new static(implode(PHP_EOL, array_map(function ($error) {
      $msg = "";

      if ($error['property']) {
        $msg .= " [{$error['property']}]";
      }

      $msg .= " {$error['message']}";

      return $msg;
    }, $errors)), 0, $previous, $errors);
  }

}
