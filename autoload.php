<?php

// Load classes first
require_once __DIR__ . '/backend/includes/classes/Config.php';
require_once __DIR__ . '/backend/includes/classes/Validator.php';
require_once __DIR__ . '/backend/includes/classes/Exceptions.php';

// Load helper functions
require_once __DIR__ . '/backend/includes/helpers.php';
require_once __DIR__ . '/backend/includes/api.php';

// Auto-load all Model classes
spl_autoload_register(function ($className) {
  // Map of class names to their file paths
  $classMap = [
    'Database' => __DIR__ . '/backend/models/Database.php',
    'User'     => __DIR__ . '/backend/models/User.php',
    'Project'  => __DIR__ . '/backend/models/Project.php',
    'Task'     => __DIR__ . '/backend/models/Task.php',
    'Comment'  => __DIR__ . '/backend/models/Comment.php',
  ];

  // If the class exists in our map, require it
  if (isset($classMap[$className]) && file_exists($classMap[$className])) {
    require_once $classMap[$className];
  }
});

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
