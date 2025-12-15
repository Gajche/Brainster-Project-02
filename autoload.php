<?php

/**
 * Simple Autoloader for Project Management System
 * Place this file in your project root and require it once
 * 
 * Usage: require_once __DIR__ . '/autoload.php';
 */

// ============================================================================
// STEP 1: Load Config first (required by everything else)
// ============================================================================
require_once __DIR__ . '/includes/config.php';

// ============================================================================
// STEP 2: Load helper functions (must load before controllers)
// ============================================================================
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/api.php';

// ============================================================================
// STEP 3: Auto-load all Model classes
// ============================================================================
spl_autoload_register(function ($className) {
  // Map of class names to their file paths
  $classMap = [
    'Database' => __DIR__ . '/models/Database.php',
    'User'     => __DIR__ . '/models/User.php',
    'Project'  => __DIR__ . '/models/Project.php',
    'Task'     => __DIR__ . '/models/Task.php',
    'Comment'  => __DIR__ . '/models/Comment.php',
  ];

  // If the class exists in our map, require it
  if (isset($classMap[$className]) && file_exists($classMap[$className])) {
    require_once $classMap[$className];
  }
});

// ============================================================================
// OPTIONAL: Start session if not already started (convenience)
// ============================================================================
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
