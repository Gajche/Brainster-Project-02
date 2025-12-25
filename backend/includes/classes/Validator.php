<?php

/**
 * Input Validation Class 
 * Centralizes all validation logic to avoid code duplication
 */
class Validator
{
  /**
   * Validate required field (not empty)
   * 
   * @param mixed $value The value to check
   * @return bool True if not empty
   */
  public static function required($value): bool
  {
    if (is_string($value)) {
      return trim($value) !== '';
    }
    return !empty($value);
  }

  /**
   * Validate email format(stronger than filter_var)
   * 
   * @param string $email Email address to validate
   * @return bool True if valid email format
   */
  public static function email(string $email): bool
  {
    return (bool) preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
  }

  /**
   * Validate password strength
   * 
   * @param string $password Password to validate
   * @param int $minLength Minimum password length (default: 6)
   * @return bool True if password meets requirements
   */
  public static function password(string $password, int $minLength = 6): bool
  {
    return strlen($password) >= $minLength;
  }

  /**
   * Validate passwords match
   * 
   * @param string $password First password
   * @param string $confirmPassword Second password
   * @return bool True if passwords match
   */
  public static function passwordsMatch(string $password, string $confirmPassword): bool
  {
    return $password === $confirmPassword;
  }

  /**
   * Validate string length
   * 
   * @param string $value String to check
   * @param int $min Minimum length
   * @param int $max Maximum length
   * @return bool True if length is within range
   */
  public static function length(string $value, int $min = 0, int $max = PHP_INT_MAX): bool
  {
    $length = strlen($value);
    return $length >= $min && $length <= $max;
  }

  /**
   * Validate value is in allowed list
   * 
   * @param mixed $value Value to check
   * @param array $allowedValues Array of allowed values
   * @return bool True if value is in allowed list
   */
  public static function inArray($value, array $allowedValues): bool
  {
    return in_array($value, $allowedValues, true);
  }

  /**
   * Validate user level
   * 
   * @param string $level User level to validate
   * @return bool True if valid user level
   */
  public static function userLevel(string $level): bool
  {
    return self::inArray($level, Config::USER_LEVELS) && $level !== 'Admin';
  }

  /**
   * Validate task status
   * 
   * @param string $status Task status to validate
   * @return bool True if valid task status
   */
  public static function taskStatus(string $status): bool
  {
    return self::inArray($status, Config::TASK_STATUSES);
  }

  /**
   * Sanitize string (prevent XSS)
   * 
   * @param string $value String to sanitize
   * @return string Sanitized string
   */
  public static function sanitize(string $value): string
  {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
  }

  /**
   * Validate multiple fields at once
   * Throws ValidationException on first failure
   * 
   * @param array $rules Array of ['field_name' => ['rule1', 'rule2'], ...]
   * @param array $data Array of field values
   * @throws ValidationException
   * @return bool True if all validations pass
   */
  public static function validate(array $rules, array $data): bool
  {
    foreach ($rules as $field => $fieldRules) {
      $value = $data[$field] ?? null;

      foreach ($fieldRules as $rule => $params) {
        // Handle simple rules (no parameters)
        if (is_int($rule)) {
          $rule = $params;
          $params = [];
        }

        // Call validation method
        $method = $rule;
        $args = is_array($params) ? array_merge([$value], $params) : [$value, $params];

        if (!call_user_func_array([self::class, $method], $args)) {
          throw new ValidationException(
            self::getErrorMessage($field, $rule, $params)
          );
        }
      }
    }

    return true;
  }

  /**
   * Get human-readable error message
   * 
   * @param string $field Field name
   * @param string $rule Validation rule
   * @param mixed $params Rule parameters
   * @return string Error message
   */
  private static function getErrorMessage(string $field, string $rule, $params): string
  {
    $fieldName = ucfirst(str_replace('_', ' ', $field));

    $messages = [
      'required' => "{$fieldName} is required.",
      'email' => "{$fieldName} must be a valid email address.",
      'password' => "{$fieldName} must be at least {$params} characters.",
      'passwordsMatch' => "Passwords do not match.",
      'length' => "{$fieldName} must be between {$params[0]} and {$params[1]} characters.",
      'inArray' => "{$fieldName} contains an invalid value.",
      'userLevel' => "Invalid user level selected.",
      'taskStatus' => "Invalid task status.",
    ];

    return $messages[$rule] ?? "{$fieldName} is invalid.";
  }

  /**
   * Comprehensive sanitization
   */
  public static function sanitizeInput($input, $type = 'string')
  {
    switch ($type) {
      case 'email':
        return filter_var($input, FILTER_SANITIZE_EMAIL);
      case 'int':
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
      case 'url':
        return filter_var($input, FILTER_SANITIZE_URL);
      case 'string':
      default:
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
  }
}
