<?php
// Front Controller: routes requests, loads controllers and views

// Ignore favicon requests
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/favicon.ico') {
  http_response_code(204);
  exit;
}

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

// Merge with router pages (optional - for consistency)
$allowedPages = array_unique(array_merge($allowedPages, Router::getAllowedPages()));

if (!in_array($page, $allowedPages)) {
  $page = isLoggedIn() ? 'dashboard' : 'login';
}

// Load the controller FIRST so it can set flash messages
Router::loadController($page);

// Handle flash messages (display once) - AFTER controller runs
$flashSuccess = $_SESSION[Config::FLASH_SUCCESS] ?? '';
$flashError   = $_SESSION[Config::FLASH_ERROR]   ?? '';

// Clear flash messages AFTER displaying them
unset($_SESSION[Config::FLASH_SUCCESS], $_SESSION[Config::FLASH_ERROR]);

// View path (backend/views/)
$viewPath = Config::BACKEND_DIR . "/views/{$page}.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Config::SITE_TITLE ?> - <?= ucfirst(str_replace('_', ' ', $page)) ?></title>

  <!-- Styles (now in frontend/) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="frontend/vendor/css/bootstrap.min.css">
  <link rel="stylesheet" href="frontend/vendor/fontawesome/css/all.min.css">

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="frontend/css/custom.css">
  <link rel="stylesheet" href="frontend/vendor/css/toastr.min.css">
  <link rel="stylesheet" href="frontend/css/kanban.css">
  <link rel="icon" href="favicon.ico" type="image/x-icon">

</head>

<body data-page="<?= $page ?>">

  <?php require_once Config::BACKEND_DIR . '/views/partials/header.php'; ?>

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
      try {
        require_once $viewPath;
      } catch (DatabaseException $e) {
        http_response_code(500);
        echo '<div class="alert alert-danger">';
        echo htmlspecialchars($e->getMessage());
        echo '</div>';
      }
    } else {
      echo '<div class="alert alert-warning">Page not found.</div>';
    }
    ?>

  </div>

  <?php require_once Config::BACKEND_DIR . '/views/partials/footer.php'; ?>

  <!-- Scripts (now in frontend/) -->
  <script src="frontend/vendor/js/jquery.min.js"></script>
  <script src="frontend/vendor/js/toastr.min.js"></script>
  <script src="frontend/vendor/js/bootstrap.bundle.min.js"></script>
  <script src="frontend/vendor/js/Sortable.min.js" async></script>

  <script type="module" src="frontend/js/main.js"></script>

  <!-- Delete Global -->
  <?php renderConditionalDeleteAssets($page); ?>

  <!-- System DB Modal -->
  <?php require_once Config::BACKEND_DIR . '/views/partials/system_db_modal.php'; ?>

</body>

</html>