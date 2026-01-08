<?php

require_once __DIR__ . '/../autoload.php';

// require_once __DIR__ . '../../backend/includes/classes/Config.php';
// require_once Config::BACKEND_DIR . '/models/Database.php';

try {
  $db = Database::getInstance();
  // Test query to verify connection
  $stmt = $db->query('SELECT 1');
  $result = $stmt->fetchColumn();
  if ($result == 1) {
    echo "Connected successfully to the database!";
  } else {
    echo "Connection test failed.";
  }
} catch (DatabaseException $e) {
  echo "Connection error: " . $e->getMessage();
}
