<?php

/**
 * Custom Exception Classes
 * Provides specific exception types for different error scenarios
 */

/**
 * Thrown when input validation fails
 */
class ValidationException extends Exception
{
  public function __construct(string $message = "Validation failed", int $code = 400)
  {
    parent::__construct($message, $code);
  }
}

/**
 * Thrown when user lacks permission for an action
 */
class PermissionException extends Exception
{
  public function __construct(string $message = "Access denied", int $code = 403)
  {
    parent::__construct($message, $code);
  }
}

/**
 * Thrown when a requested resource is not found
 */
class NotFoundException extends Exception
{
  public function __construct(string $message = "Resource not found", int $code = 404)
  {
    parent::__construct($message, $code);
  }
}

/**
 * Thrown when authentication fails
 */
class AuthenticationException extends Exception
{
  public function __construct(string $message = "Authentication failed", int $code = 401)
  {
    parent::__construct($message, $code);
  }
}



class DatabaseException extends Exception
{
  public function __construct(
    string $message = "Database error occurred",
    int $code = 500,
    ?Throwable $previous = null
  ) {
    parent::__construct($message, $code, $previous);
  }
}
