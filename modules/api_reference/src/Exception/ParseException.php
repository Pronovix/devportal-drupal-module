<?php

namespace Drupal\devportal_api_reference\Exception;

/**
 * Copyright (C) 2019 PRONOVIX GROUP BVBA.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,
 * USA.
 */

/**
 * Thrown if the input file could not be parsed.
 */
class ParseException extends RuntimeException {

  /**
   * The type of the file.
   *
   * @var string
   */
  private $fileType;

  /**
   * ParseException constructor.
   *
   * @param string $file_type
   *   The type of the file.
   * @param string $path
   *   The path of the file.
   * @param string $reason
   *   The reason.
   * @param int|null $code
   *   The code.
   * @param \Throwable|null $previous_exception
   *   The previous exception or NULL.
   */
  public function __construct(string $file_type, string $path, string $reason, int $code = NULL, \Throwable $previous_exception = NULL) {
    $file_type = strtoupper($file_type);
    $this->fileType = $file_type;
    $code = $code ?? 0;
    parent::__construct("The {$file_type} source at {$path} path could not be parsed because: {$reason}.", $code, $previous_exception);
  }

  /**
   * Constructs a YAML parse error.
   *
   * @param string $path
   *   The path of the file.
   * @param string $reason
   *   The reason.
   * @param \Throwable|null $previous_exception
   *   The previous exception or NULL.
   *
   * @return self
   *   The ParseException.
   */
  public static function yamlParseError(string $path, string $reason, \Throwable $previous_exception = NULL): self {
    return new static('YAML', $path, $reason, NULL, $previous_exception);
  }

  /**
   * Constructs a JSON parse error.
   *
   * @param string $path
   *   The path of the file.
   * @param int $json_last_error_code
   *   The last json error code.
   * @param string $json_last_error_message
   *   The last json error message.
   * @param \Throwable|null $previous_exception
   *   The previous exception or NULL.
   *
   * @return self
   *   The ParseException.
   */
  public static function jsonParseError(string $path, int $json_last_error_code, string $json_last_error_message, \Throwable $previous_exception = NULL): self {
    return new static('JSON', $path, $json_last_error_message, $json_last_error_code, $previous_exception);
  }

  /**
   * Get the file type.
   *
   * @return string
   *   The type of the file.
   */
  public function getFileType(): string {
    return $this->fileType;
  }

}
