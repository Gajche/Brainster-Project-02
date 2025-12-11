<?php
// autoload.php — Simple, fast, zero-config class loader
spl_autoload_register(function ($class) {
  // Convert namespace to file path
  // App\Models\User → src/Models/User.php
  $prefix = 'App\\';
  $base_dir = __DIR__ . '/src/';

  if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
    return;
  }

  $relative_class = substr($class, strlen($prefix));
  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  if (file_exists($file)) {
    require_once $file;
  }
});
