<?php

// Function to check if a user is logged in
function isLoggedIn()
{
  return isset($_SESSION['user_id']);
}

// Function to check if the logged-in user is an Admin
function isAdmin()
{
  return isset($_SESSION['user_level']) && $_SESSION['user_level'] === 'Admin';
}

/**
 * Detect if the current request is AJAX
 */
function isAjax()
{
  return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Handle response for both AJAX and traditional requests
 * Automatically sets session flash and returns appropriate response
 */
function handleResponse($success, $message, $data = null, $httpCode = 200)
{
  // Always set session flash for Bootstrap alerts after page reload
  if ($success) {
    $_SESSION[Config::FLASH_SUCCESS] = $message;
  } else {
    $_SESSION[Config::FLASH_ERROR] = $message;
  }

  // Return AJAX response or redirect
  if (isAjax()) {
    if ($success) {
      apiSuccess($data, $message);
    } else {
      apiError('error', $message, $httpCode);
    }
  } else {
    redirectBack();
  }
}

/**
 * Redirect back to the previous page
 */
function redirectBack()
{
  $referer = $_SERVER['HTTP_REFERER'] ?? Config::getBaseUrl() . 'index.php?page=dashboard';
  header('Location: ' . $referer);
  exit;
}

/**
 * Summary of renderConditionalDeleteAssets
 * @param mixed $page
 * @return void
 */
// Conditionally include delete confirmation modal and script
function renderConditionalDeleteAssets($page)
{
  $pagesNeedingDelete = ['admin_projects', 'admin_users', 'project_view', 'task_view'];

  if (in_array($page, $pagesNeedingDelete)): ?>
    <!-- Reusable Delete Confirmation Modal -->
    <?php include Config::BACKEND_DIR . '/views/partials/confirm_modal.php'; ?>
    <!-- Confirm Delete Handler -->
    <script src="<?= htmlspecialchars(Config::getBaseUrl() . 'frontend/js/confirmDelete.js') ?>"></script>
<?php endif;
}
