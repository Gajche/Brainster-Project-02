<?php

class Config
{
  const DB_HOST = 'localhost';
  const DB_NAME = 'my_project_management';
  const DB_USER = 'root';
  const DB_PASS = '';

  const ROOT_DIR = __DIR__ . '/..';  // Points to project root folder (adjust if config moves)

  const SITE_TITLE = 'Project Management Software';

  // const BASE_URL = 'http://localhost/project_management/';  

  const BASE_URL = '';  // Compute it dynamically 

  // Dynamic BASE_URL getter (call as Config::getBaseUrl())
  public static function getBaseUrl()
  {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];

    // Generically remove /controllers/anyfile.php from the path
    $basePath = preg_replace('/\/controllers\/[^\/]*$/', '', $script_name);

    // If the regex didn't match (i.e., we are not in a controller), just get the dirname
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

  const USER_LEVELS = ['Admin', 'Senior', 'Mid', 'Junior'];

  const TASK_STATUSES = ['To Do', 'In Progress', 'QA', 'Done'];

  const PROJECT_STATUSES = ['Active', 'Done'];

  const FLASH_SUCCESS = 'success';
  const FLASH_ERROR = 'error';
}

// Prevent direct access to this file
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  die('Access denied');
}
