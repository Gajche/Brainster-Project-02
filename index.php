<?php


// require_once 'includes/config.php';  // Use relative for root
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once Config::ROOT_DIR . '/models/Database.php';  // For any DB needs, but mostly in controllers

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Get page from GET, default based on login
$page = $_GET['page'] ?? (isLoggedIn() ? 'dashboard' : 'login');

// Whitelist pages to prevent inclusion attacks
$allowedPages = [
  'login',
  'register',
  'change_password',
  'dashboard',
  'project_view',
  'task_view',
  'admin_panel',
  'admin_users',
  'admin_projects'
];

if (!in_array($page, $allowedPages)) {
  $page = isLoggedIn() ? 'dashboard' : 'login';
}

// Handle flash messages (display once)
$flashSuccess = $_SESSION[Config::FLASH_SUCCESS] ?? '';
$flashError = $_SESSION[Config::FLASH_ERROR] ?? '';
unset($_SESSION[Config::FLASH_SUCCESS], $_SESSION[Config::FLASH_ERROR]);

// Include view
$viewPath = Config::ROOT_DIR . "/views/{$page}.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Config::SITE_TITLE ?> - <?= ucfirst($page) ?></title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="css/kanban.css">
</head>

<body>

  <?php require_once Config::ROOT_DIR . '/includes/header.php'; ?>

  <div class="container mt-4">
    <?php if ($flashSuccess): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flashSuccess) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flashError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php
    if (file_exists($viewPath)) {
      require_once $viewPath;
    } else {
      echo '<div class="alert alert-warning">Page not found.</div>';
    }
    ?>
  </div>

  <?php require_once Config::ROOT_DIR . '/includes/footer.php'; ?>

  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/Sortable.min.js"></script>
  <script type="module" src="js/main.js"></script>
</body>

</html>