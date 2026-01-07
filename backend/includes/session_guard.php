<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['user_id'])) {
  return;
}

$now = time();

if (
  isset($_SESSION['last_activity']) &&
  ($now - $_SESSION['last_activity']) > Config::SESSION_TIMEOUT
) {
  session_unset();
  session_destroy();

  session_start();
  session_regenerate_id(true);

  if (isAjax()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
      'success' => false,
      'error'   => 'session_expired',
      'message' => 'Your session has expired. Please log in again.'
    ]);
    exit;
  }

  $_SESSION[Config::FLASH_ERROR] =
    'Your session has expired due to inactivity. Please log in again.';

  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

$_SESSION['last_activity'] = $now;
