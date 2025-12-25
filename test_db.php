<?php

// test_db.php

require_once __DIR__ . '../backend/includes/config.php';
require_once Config::BACKEND_DIR . '/models/Database.php';

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
} catch (PDOException $e) {
  echo "Connection error: " . $e->getMessage();
}
