<?php

require_once __DIR__ . '/autoload.php';

// Determine which page to show
$page = $_GET['page'] ?? (isLoggedIn() ? 'dashboard' : 'login');

// Whitelist allowed pages (security against directory traversal)
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
$flashError   = $_SESSION[Config::FLASH_ERROR]   ?? '';
unset($_SESSION[Config::FLASH_SUCCESS], $_SESSION[Config::FLASH_ERROR]);

// Resolve view path
$viewPath = Config::ROOT_DIR . "/views/{$page}.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Config::SITE_TITLE ?> - <?= ucfirst(str_replace('_', ' ', $page)) ?></title>

  <!-- Styles -->
  <link rel="stylesheet" href="vendor/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="vendor/css/toastr.min.css">
  <link rel="stylesheet" href="css/kanban.css">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body data-page="<?= $page ?>">

  <?php require_once Config::ROOT_DIR . '/includes/header.php'; ?>

  <div class="container mt-4">

    <!-- Flash Success Message -->
    <?php if ($flashSuccess): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flashSuccess) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Flash Error Message -->
    <?php if ($flashError): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flashError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Load the requested view -->
    <?php
    if (file_exists($viewPath)) {
      require_once $viewPath;
    } else {
      echo '<div class="alert alert-warning">Page not found.</div>';
    }
    ?>

  </div>

  <?php require_once Config::ROOT_DIR . '/includes/footer.php'; ?>

  <!-- Scripts -->
  <script src="vendor/js/jquery.min.js"></script>
  <script src="vendor/js/toastr.min.js"></script>
  <script src="vendor/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/js/Sortable.min.js" async></script>

  <script type="module" src="js/main.js"></script>

</body>

</html>