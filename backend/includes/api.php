<?php

// Common API response helpers for AJAX requests
if (!function_exists('apiSuccess')) {
  function apiSuccess(mixed $data = null, string $message = 'Operation successful'): void
  {
    if (
      isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'success' => true,
        'data'    => $data,
        'message' => $message
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      exit;
    }
  }
}

// Common API error response helper
if (!function_exists('apiError')) {
  function apiError(
    string $errorKey,
    string $message = 'An error occurred',
    int $httpCode = 400,
    mixed $data = null
  ): void {
    if (
      isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
      http_response_code($httpCode);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'success' => false,
        'error'   => $errorKey,
        'message' => $message,
        'data'    => $data
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      exit;
    }

    // NON-AJAX FALLBACK 
    if (session_status() === PHP_SESSION_ACTIVE) {
      $_SESSION[Config::FLASH_ERROR] = $message;
    }
    $base = class_exists('Config') && method_exists('Config', 'getBaseUrl')
      ? Config::getBaseUrl()
      : '';
    $page = $_POST['action'] ?? 'dashboard';
    header("Location: {$base}index.php?page={$page}");
    exit;
  }
}

// Common API session expired response helper
if (!function_exists('apiSessionExpired')) {
  function apiSessionExpired(): void
  {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
      'success' => false,
      'error'   => 'session_expired',
      'message' => 'Your session has expired. Please log in again.'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
  }
}
