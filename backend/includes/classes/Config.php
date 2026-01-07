<?php

// SESSION SECURITY HARDENING SETTINGS
// Prevent JS access to session cookie
ini_set('session.cookie_httponly', 1);

// Only send cookies over HTTPS (safe fallback for localhost)
ini_set(
  'session.cookie_secure',
  !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
);

// Prevent session fixation
ini_set('session.use_strict_mode', 1);


// Config class
class Config
{
  const DB_HOST = 'localhost';
  const DB_NAME = 'nikolovski_project_management';
  const DB_USER = 'root';
  const DB_PASS = '';

  // Session inactivity timeout (in seconds)

  // const SESSION_TIMEOUT = 10; // 10 seconds test
  const SESSION_TIMEOUT = 1800; // 30 minutes

  // ROOT_DIR now points to project root (two levels up from backend/includes/)
  const ROOT_DIR = __DIR__ . '/../../..';

  // Backend directory
  const BACKEND_DIR = __DIR__ . '/../..';

  // Frontend directory  
  const FRONTEND_DIR = self::ROOT_DIR . '/frontend';

  const SITE_TITLE = 'Project Management Application';

  const BASE_URL = '';  // Compute it dynamically 

  // Dynamic BASE_URL getter (call as Config::getBaseUrl())
  public static function getBaseUrl()
  {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];

    // Remove /backend/controllers/anyfile.php from the path
    $basePath = preg_replace('/\/backend\/controllers\/[^\/]*$/', '', $script_name);

    // If the regex didn't match (we are not in a backend controller), just get the dirname
    if ($basePath === $script_name) {
      $basePath = dirname($script_name);
    }

    // Clean up trailing slashes and handle root directory case
    $basePath = rtrim($basePath, '/\\');
    if ($basePath === '') {
      return $protocol . $host . '/';
    }

    return $protocol . $host . $basePath . '/';
  }

  const USER_LEVELS = ['Senior', 'Mid', 'Junior'];

  const TASK_STATUSES = ['To Do', 'In Progress', 'QA', 'Done'];

  const PROJECT_STATUSES = ['Active', 'Done'];

  const FLASH_SUCCESS = 'success';
  const FLASH_ERROR = 'error';
}

// Prevent direct access to this file
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  die('Access denied');
}
