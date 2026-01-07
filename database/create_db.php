<?php

// DEV-ONLY GUARD 
if (
  php_sapi_name() !== 'cli' &&
  !preg_match('/^(localhost|127\.0\.0\.1)(\:\d+)?$/', $_SERVER['HTTP_HOST'] ?? '')
) {
  http_response_code(403);
  exit('Forbidden: Database setup is DEV-only.');
}

require_once __DIR__ . '/classes/DatabaseInitializer.php';

header('Content-Type: application/json'); // Always return JSON for better AJAX handling

try {
  DatabaseInitializer::run();

  http_response_code(200);
  echo json_encode([
    'success' => true,
    'message' => 'Database initialized successfully.'
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
