<?php

require_once __DIR__ . '/../../autoload.php';

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Ensure user is Admin
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'Admin') {
  $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';


try {
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
      // Throw exception for consistency instead of direct redirect
      throw new ValidationException('Invalid action.');
  }
} catch (ValidationException $e) {
  // Handle validation errors (business rules, required fields, etc.)
  if (isAjax()) {
    handleResponse(false, $e->getMessage(), null, 400);
  } else {
    $_SESSION[Config::FLASH_ERROR] = $e->getMessage();
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  }
  exit;
} catch (Exception $e) {
  // Handle all unexpected errors (database, system errors, etc.)
  error_log("User controller error [Admin: {$_SESSION['user_id']}]: " . $e->getMessage());

  if (isAjax()) {
    handleResponse(false, 'An unexpected error occurred. Please try again.', null, 500);
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'An unexpected error occurred. Please try again.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  }
  exit;
}


/**
 * Handles the 'reset_password' action for a user.
 */
function handleResetPassword()
{
  $id = $_POST['id'] ?? 0;
  $adminId = $_SESSION['user_id'] ?? null; // Get admin ID

  if (empty($id)) {
    handleResponse(false, 'Invalid user ID.', null, 400);
    return;
  }

  $newPw = User::adminResetPassword($id, $adminId);
  if ($newPw) {
    $successMessage = '(HIGH SECURITY RISK. DO NOT EXPOSE PASSWORD IN AJAX RESPONSE IN PRODUCTION. Password exposed on purpose) Password reset successfully. New password: ' . $newPw;
    handleResponse(true, $successMessage);
  } else {
    handleResponse(false, 'Password reset failed.', null, 500);
  }
}

/**
 * Handles the 'create' action for a new user.
 */
function handleCreate()
{
  $name         = trim($_POST['name'] ?? '');
  $email        = trim($_POST['email'] ?? '');
  $level        = $_POST['level'] ?? '';
  $is_team_lead = isset($_POST['is_team_lead']) ? 1 : 0;

  // Validation
  if (empty($name) || empty($email) || empty($level)) {
    handleResponse(false, 'All fields are required.', null, 400);
    return;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    handleResponse(false, 'Invalid email format.', null, 400);
    return;
  }

  if (!in_array($level, Config::USER_LEVELS)) {
    handleResponse(false, 'Invalid user level.', null, 400);
    return;
  }

  // Prevent creating Admin users via UI
  if ($level === 'Admin') {
    handleResponse(false, 'Cannot create Admin users via UI. Admins must be added directly in the database.', null, 400);
    return;
  }

  // Business rule: Only Senior users can be designated as Team Leads.
  if ($is_team_lead && $level !== 'Senior') {
    handleResponse(false, 'Only Senior users can be Team Leads.', null, 400);
    return;
  }

  // Check if email already exists
  if (User::getByLogin($email)) {
    handleResponse(false, 'Email already exists.', null, 400);
    return;
  }

  // Get admin ID from session
  $adminId = $_SESSION['user_id'] ?? null;

  // Create user
  $result = User::adminCreate($name, $email, null, $level, $is_team_lead, $adminId);
  if ($result) {
    $successMessage = '(HIGH SECURITY RISK. DO NOT EXPOSE PASSWORD IN AJAX RESPONSE IN PRODUCTION. Password exposed on purpose) User created successfully. Random password: ' . $result['random_pw'];
    handleResponse(true, $successMessage);
  } else {
    handleResponse(false, 'User creation failed.', null, 500);
  }
}

/**
 * Handles the 'update' action for an existing user.
 */
function handleUpdate()
{
  $id   = $_POST['id'] ?? 0;
  $data = [];

  // Populate data from POST
  if (isset($_POST['name']))  $data['name']  = trim($_POST['name']);
  if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
  if (isset($_POST['level'])) $data['level'] = $_POST['level'];
  $data['is_team_lead'] = isset($_POST['is_team_lead']) ? 1 : 0;

  if (empty($id) || empty($data)) {
    handleResponse(false, 'Invalid data.', null, 400);
    return;
  }

  // Prevent promoting to Admin
  if (isset($data['level']) && $data['level'] === 'Admin') {
    handleResponse(false, 'Cannot promote users to Admin via UI. Admins must be added directly in the database.', null, 400);
    return;
  }

  // Normalize Team Lead flag
  $level = $data['level'] ?? null;

  // If level not provided, get current level
  if (!$level) {
    $existingUser = User::getById($id);
    if (!$existingUser) {
      handleResponse(false, 'User not found.', null, 404);
      return;
    }
    $level = $existingUser->getLevel();
  }

  // FORCE rule: only Seniors can be Team Leads
  if ($level !== 'Senior') {
    $data['is_team_lead'] = 0;
  }

  // Update user
  if (User::update($id, $data)) {
    handleResponse(true, 'User updated successfully.');
  } else {
    handleResponse(false, 'User update failed.', null, 500);
  }
}

/**
 * Handles the 'approve' action for a user.
 */
function handleApprove()
{
  $id = $_POST['id'] ?? 0;

  if (empty($id)) {
    handleResponse(false, 'Invalid user ID.', null, 400);
    return;
  }

  if (User::approve($id)) {
    handleResponse(true, 'User approved successfully.');
  } else {
    handleResponse(false, 'User approval failed.', null, 500);
  }
}

/**
 * Handles the 'delete' action for a user.
 */
function handleDelete()
{
  $id = $_POST['id'] ?? 0;

  // Prevent deleting yourself
  if (empty($id) || $id == $_SESSION['user_id']) {
    handleResponse(false, 'Cannot delete yourself.', null, 400);
    return;
  }

  // Get user to delete
  $userToDelete = User::getById($id);

  if (!$userToDelete) {
    handleResponse(false, 'User not found.', null, 404);
    return;
  }

  // Check if user is an Admin
  if ($userToDelete->getLevel() === 'Admin') {
    // Count how many admins exist
    $allUsers = User::getAll();
    $adminCount = 0;

    foreach ($allUsers as $userData) {
      if (is_array($userData) && isset($userData['level']) && $userData['level'] === 'Admin') {
        $adminCount++;
      } elseif (is_object($userData) && $userData->getLevel() === 'Admin') {
        $adminCount++;
      }
    }

    // Prevent deleting the last admin
    if ($adminCount <= 1) {
      handleResponse(false, 'Cannot delete the last admin.', null, 400);
      return;
    }
  }

  // Delete user
  if (User::delete($id)) {
    handleResponse(true, 'User deleted successfully.');
  } else {
    handleResponse(false, 'Failed to delete user.', null, 500);
  }
}
