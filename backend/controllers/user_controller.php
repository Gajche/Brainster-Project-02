<?php

require_once __DIR__ . '/../../autoload.php';

// require_once __DIR__ . '/../includes/config.php';
// require_once __DIR__ . '/../includes/api.php';
// require_once Config::ROOT_DIR . '/models/Database.php';
// require_once Config::ROOT_DIR . '/models/User.php';

// Start session if not already
// Ensures a user session is active to manage state and access control.
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Ensure user is Admin
// This block enforces an access control policy, redirecting non-Admin users.
// Only users with 'Admin' level are permitted to access and perform actions on this page.
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'Admin') {
  $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
  // Redirects unauthorized users to the dashboard.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

// Helper: Send JSON response if request is AJAX, otherwise continue with redirect
/**
 * Handles responses for form submissions, differentiating between AJAX and standard requests.
 * For AJAX requests, it sends a JSON response and terminates execution.
 * For non-AJAX requests, it allows the script to continue to a standard HTTP redirect.
 *
 * @param bool $success Indicates whether the operation was successful.
 * @param string $message The message to be displayed to the user.
 * @return void This function may terminate script execution for AJAX requests.
 */
function ajaxRespondAndRedirect(bool $success, string $message): void
{
  // Checks for the `HTTP_X_REQUESTED_WITH` header, a common indicator for AJAX requests.
  if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
  ) {
    header('Content-Type: application/json');
    echo json_encode([
      'success' => $success,
      'message' => $message
    ]);
    exit; // Terminate script to prevent further output for AJAX requests.
  }
}

// Handle actions
// Determines the requested action from POST or GET parameters.
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Dispatches the request to the appropriate handler function based on the 'action' parameter.
switch ($action) {
  case 'create':
    handleCreate();
    break;
  case 'update':
    handleUpdate();
    break;
  case 'approve':
    handleApprove();
    break;
  case 'delete':
    handleDelete();
    break;
  case 'reset_password':
    handleResetPassword();
    break;
  default:
    // If no valid action is provided, redirects to the admin users listing page.
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
}

/**
 * Handles the 'reset_password' action for a user.
 * Resets the password for a specified user ID to a new random password.
 * Updates session flash messages and responds appropriately (AJAX or redirect).
 *
 * @return void
 */
function handleResetPassword()
{
  // Retrieves the user ID from the POST request, defaulting to 0 if not set.
  $id = $_POST['id'] ?? 0;

  // Validates if a user ID was provided.
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user ID.';
    ajaxRespondAndRedirect(false, 'Invalid user ID.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Attempts to reset the user's password using the User model.
  // The method returns the new plain-text password on success.
  $newPw = User::adminResetPassword($id);
  if ($newPw) {
    $successMessage = 'Password reset successfully. New password: ' . $newPw;
    $_SESSION[Config::FLASH_SUCCESS] = $successMessage;
    ajaxRespondAndRedirect(true, $successMessage);
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Password reset failed.';
    ajaxRespondAndRedirect(false, 'Password reset failed.');
  }

  // Redirects to the admin users page after the operation.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

/**
 * Handles the 'create' action for a new user.
 * Validates input data, prevents creation of Admin users via UI, and ensures business rules (e.g., Team Lead status).
 * Creates a new user in the database with a randomly generated password.
 * Updates session flash messages and responds appropriately (AJAX or redirect).
 *
 * @return void
 */
function handleCreate()
{
  // Retrieves and sanitizes user input for new user creation.
  $name         = trim($_POST['name'] ?? '');
  $email        = trim($_POST['email'] ?? '');
  $level        = $_POST['level'] ?? '';
  // Converts checkbox value to integer (1 for checked, 0 for unchecked).
  $is_team_lead = isset($_POST['is_team_lead']) ? 1 : 0;

  // Validates if all required fields are provided.
  if (empty($name) || empty($email) || empty($level)) {
    $_SESSION[Config::FLASH_ERROR] = 'All fields are required.';
    ajaxRespondAndRedirect(false, 'All fields are required.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Validates the email format.
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid email format.';
    ajaxRespondAndRedirect(false, 'Invalid email format.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Validates the provided user level against a predefined list of valid levels.
  if (!in_array($level, Config::USER_LEVELS)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user level.';
    ajaxRespondAndRedirect(false, 'Invalid user level.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Prevent creating Admin users via UI
  // Enforces a security policy that Admin accounts cannot be created through the web interface.
  if ($level === 'Admin') {
    $_SESSION[Config::FLASH_ERROR] = 'Cannot create Admin users via UI. Admins must be added directly in the database.';
    ajaxRespondAndRedirect(false, 'Cannot create Admin users via UI.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Business rule: Only Senior users can be designated as Team Leads.
  if ($is_team_lead && $level !== 'Senior') {
    $_SESSION[Config::FLASH_ERROR] = 'Only Senior users can be Team Leads.';
    ajaxRespondAndRedirect(false, 'Only Senior users can be Team Leads.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Checks if a user with the given email already exists to prevent duplicate accounts.
  if (User::getByLogin($email)) {
    $_SESSION[Config::FLASH_ERROR] = 'Email already exists.';
    ajaxRespondAndRedirect(false, 'Email already exists.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Attempts to create the new user in the database.
  // Returns an array containing the new user's ID and random password on success.
  $result = User::adminCreate($name, $email, null, $level, $is_team_lead);
  if ($result) {
    $successMessage = 'User created successfully. Random password: ' . $result['random_pw'];
    $_SESSION[Config::FLASH_SUCCESS] = $successMessage;
    ajaxRespondAndRedirect(true, $successMessage);
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'User creation failed.';
    ajaxRespondAndRedirect(false, 'User creation failed.');
  }

  // Redirects to the admin users page after the operation.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

/**
 * Handles the 'update' action for an existing user.
 * Retrieves and validates user ID and data, applies business rules (e.g., preventing Admin promotion, Team Lead level dependency).
 * Updates the user's information in the database.
 * Updates session flash messages and responds appropriately (AJAX or redirect).
 *
 * @return void
 */
function handleUpdate()
{
  // Retrieves the user ID from the POST request.
  $id   = $_POST['id'] ?? 0;
  // Initializes an empty array to store update data.
  $data = [];

  // Populates the $data array with fields that were provided in the POST request.
  if (isset($_POST['name']))  $data['name']  = trim($_POST['name']);
  if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
  if (isset($_POST['level'])) $data['level'] = $_POST['level'];
  // Sets the 'is_team_lead' flag based on checkbox presence.
  $data['is_team_lead'] = isset($_POST['is_team_lead']) ? 1 : 0;

  // Validates if a user ID and any update data were provided.
  if (empty($id) || empty($data)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
    ajaxRespondAndRedirect(false, 'Invalid data.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Prevent promoting to Admin
  // Enforces a security policy that users cannot be promoted to Admin through the web interface.
  if (isset($data['level']) && $data['level'] === 'Admin') {
    $_SESSION[Config::FLASH_ERROR] = 'Cannot promote users to Admin via UI. Admins must be added directly in the database.';
    ajaxRespondAndRedirect(false, 'Cannot promote users to Admin via UI.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Normalize Team Lead flag
  // Determines the user's level to apply the Team Lead business rule.
  $level = $data['level'] ?? null;

  // If the 'level' was not explicitly passed in the update data, fetch the current level from the database.
  if (!$level) {
    $existingUser = User::getById($id);
    $level = $existingUser ? $existingUser->getLevel() : null;
  }

  // FORCE rule: only Seniors can be Team Leads
  // If the user's current or new level is not 'Senior', the 'is_team_lead' flag is automatically reset to 0.
  if ($level !== 'Senior') {
    $data['is_team_lead'] = 0;
  }

  // Attempts to update the user's information in the database.
  if (User::update($id, $data)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User updated successfully.';
    ajaxRespondAndRedirect(true, 'User updated successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'User update failed.';
    ajaxRespondAndRedirect(false, 'User update failed.');
  }

  // Redirects to the admin users page after the operation.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

/**
 * Handles the 'approve' action for a user.
 * Approves a user account specified by ID.
 * Updates session flash messages and responds appropriately (AJAX or redirect).
 *
 * @return void
 */
function handleApprove()
{
  // Retrieves the user ID from the POST request.
  $id = $_POST['id'] ?? 0;

  // Validates if a user ID was provided.
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user ID.';
    ajaxRespondAndRedirect(false, 'Invalid user ID.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Attempts to approve the user in the database.
  if (User::approve($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User approved successfully.';
    ajaxRespondAndRedirect(true, 'User approved successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'User approval failed.';
    ajaxRespondAndRedirect(false, 'User approval failed.');
  }

  // Redirects to the admin users page after the operation.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

/**
 * Handles the 'delete' action for a user.
 * Implements critical safeguards: prevents self-deletion and deletion of the last Admin user.
 * Deletes the specified user from the database.
 * Updates session flash messages and responds appropriately (AJAX or redirect).
 *
 * @return void
 */
function handleDelete()
{
  // Retrieves the user ID from the POST request.
  $id = $_POST['id'] ?? 0;

  // Prevent deleting yourself
  // Enforces a critical security rule: an admin cannot delete their own account.
  if (empty($id) || $id == $_SESSION['user_id']) {
    $_SESSION[Config::FLASH_ERROR] = 'Cannot delete yourself.';
    ajaxRespondAndRedirect(false, 'Cannot delete yourself.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Get user to delete
  // Fetches the details of the user intended for deletion.
  $userToDelete = User::getById($id);

  // Check if user is an Admin
  // Additional safeguard: if the target user is an Admin, further checks are performed.
  if ($userToDelete && $userToDelete->getLevel() === 'Admin') {
    // Count how many admins exist
    // Retrieves all users to determine the current count of Admin accounts.
    $allUsers = User::getAll();
    $adminCount = 0;

    // Iterates through all users to count those with 'Admin' level.
    foreach ($allUsers as $userData) {
      // Handle both array and object formats returned by User::getAll() for level checking.
      if (is_array($userData) && isset($userData['level']) && $userData['level'] === 'Admin') {
        $adminCount++;
      } elseif (is_object($userData) && $userData->getLevel() === 'Admin') {
        $adminCount++;
      }
    }

    // Prevent deleting the last admin
    // Enforces a critical system integrity rule: prevents deleting the sole remaining Admin account.
    if ($adminCount <= 1) {
      $_SESSION[Config::FLASH_ERROR] = 'Cannot delete the last admin.';
      ajaxRespondAndRedirect(false, 'Cannot delete the last admin.');
      header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
      exit;
    }
  }

  // Attempts to delete the user from the database.
  if (User::delete($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User deleted successfully.';
    ajaxRespondAndRedirect(true, 'User deleted successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Failed to delete user.';
    ajaxRespondAndRedirect(false, 'Failed to delete user.');
  }

  // Redirects to the admin users page after the operation.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}
