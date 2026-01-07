<?php

require_once __DIR__ . '/../../autoload.php';

// Auth (Admin only)
if (!isAdmin()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

// Use DatabaseHelper to safely get users (handles database exceptions automatically)
$users = DatabaseHelper::safeGetUsers(); // Fetch once, safely
$levels = Config::USER_LEVELS; // For dropdowns


$_SESSION['admin_users_data'] = compact('users', 'levels');
