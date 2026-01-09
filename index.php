<?php
// Front Controller: routes requests, loads controllers and views

// Start output buffering to prevent "headers already sent" errors
ob_start();

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

  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23007bff%22/%3E%3Ctext x=%2250%22 y=%2250%22 font-size=%2238%22 font-weight=%22bold%22 font-family=%22Arial, sans-serif%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 fill=%22%23ffffff%22%3EPMA%3C/text%3E%3C/svg%3E">

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
        // Only set HTTP code if headers not sent
        if (!headers_sent()) {
          http_response_code(500);
        }

        echo '<div class="alert alert-danger">';
        echo '<strong><i class="fas fa-database me-2"></i>Database Connection Failed</strong><br>';
        echo htmlspecialchars($e->getMessage());
        echo '<div class="mt-3">';
        echo '<small class="text-muted">';
        echo '<strong><i class="fas fa-tools me-1"></i>Troubleshooting:</strong><br>';
        echo '• Ensure XAMPP is running (MySQL & Apache started)<br>';
        echo '• Check <a href="http://localhost/phpmyadmin" target="_blank" class="text-decoration-none">';
        echo '<i class="fas fa-external-link-alt me-1"></i>phpMyAdmin</a> to verify the database and tables exist<br>';
        echo '• If tables are missing or deleted, reinitialize the database<br>';
        echo '• Refresh your browser after fixing';
        echo '</small>';
        echo '</div>';
        echo '</div>';
      }
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

  <?php ob_end_flush(); // Flush output buffer 
  ?>
</body>

</html>