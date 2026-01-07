<?php

class Router
{
  private static $routes = [
    'dashboard' => 'dashboard_controller.php',
    'project_view' => 'project_view_controller.php',
    'admin_users' => 'admin_users_controller.php',
    'admin_projects' => 'admin_projects_controller.php',
    'task_view' => 'task_view_controller.php',
    // Add more routes here
  ];

  // Load the controller for the given page, if it exists
  public static function loadController($page)
  {
    if (isset(self::$routes[$page])) {
      $controllerFile = Config::BACKEND_DIR . '/controllers/' . self::$routes[$page];
      if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return true;
      }
    }
    return false; // No controller needed
  }

  public static function addRoute($page, $controller)
  {
    self::$routes[$page] = $controller;
  }

  public static function getAllowedPages()
  {
    return array_keys(self::$routes);
  }
}
