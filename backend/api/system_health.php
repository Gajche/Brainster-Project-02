<?php

require_once __DIR__ . '/../../autoload.php';

// Only allow AJAX requests
if (!isAjax()) {
  http_response_code(403);
  exit;
}

// Check database connection
try {
  Database::getInstance();
  apiSuccess();
} catch (DatabaseException $e) {
  apiError(
    'db_missing',
    'Database not found or connection failed.',
    503
  );
}
