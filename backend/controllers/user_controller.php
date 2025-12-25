<?php

require_once __DIR__ . '/../../autoload.php';

// require_once __DIR__ . '/../includes/config.php';
// require_once __DIR__ . '/../includes/api.php';
// require_once Config::ROOT_DIR . '/models/Database.php';
// require_once Config::ROOT_DIR . '/models/User.php';

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

// Helper: Send JSON response if request is AJAX, otherwise continue with redirect
function ajaxRespondAndRedirect(bool $success, string $message): void
{
  if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
  ) {
    header('Content-Type: application/json');
    echo json_encode([
      'success' => $success,
      'message' => $message
    ]);
    exit;
  }
}

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

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
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
}


// ADMIN USER ACTIONS
// function handleResetPassword()
// {
//   $id = $_POST['id'] ?? 0;

//   if (empty($id)) {
//     $_SESSION[Config::FLASH_ERROR] = 'Invalid user ID.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   $newPw = User::adminResetPassword($id);
//   if ($newPw) {
//     $_SESSION[Config::FLASH_SUCCESS] = 'Password reset successfully. New password: ' . $newPw;
//   } else {
//     $_SESSION[Config::FLASH_ERROR] = 'Password reset failed.';
//   }
//   header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//   exit;
// }

function handleResetPassword()
{
  $id = $_POST['id'] ?? 0;

  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user ID.';
    ajaxRespondAndRedirect(false, 'Invalid user ID.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  $newPw = User::adminResetPassword($id);
  if ($newPw) {
    $successMessage = 'Password reset successfully. New password: ' . $newPw;
    $_SESSION[Config::FLASH_SUCCESS] = $successMessage;
    ajaxRespondAndRedirect(true, $successMessage);
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Password reset failed.';
    ajaxRespondAndRedirect(false, 'Password reset failed.');
  }

  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

// function handleCreate()
// {
//   $name         = trim($_POST['name'] ?? '');
//   $email        = trim($_POST['email'] ?? '');
//   $level        = $_POST['level'] ?? '';
//   $is_team_lead = isset($_POST['is_team_lead']) ? 1 : 0;

//   if (empty($name) || empty($email) || empty($level)) {
//     $_SESSION[Config::FLASH_ERROR] = 'All fields are required.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//     $_SESSION[Config::FLASH_ERROR] = 'Invalid email format.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   if (!in_array($level, Config::USER_LEVELS)) {
//     $_SESSION[Config::FLASH_ERROR] = 'Invalid user level.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   // Prevent creating Admin users via UI
//   if ($level === 'Admin') {
//     $_SESSION[Config::FLASH_ERROR] = 'Cannot create Admin users via UI. Admins must be added directly in the database.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   if ($is_team_lead && $level !== 'Senior') {
//     $_SESSION[Config::FLASH_ERROR] = 'Only Senior users can be Team Leads.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   if (User::getByLogin($email)) {
//     $_SESSION[Config::FLASH_ERROR] = 'Email already exists.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   $result = User::adminCreate($name, $email, null, $level, $is_team_lead);
//   if ($result) {
//     $_SESSION[Config::FLASH_SUCCESS] = 'User created successfully. Random password: ' . $result['random_pw'];
//   } else {
//     $_SESSION[Config::FLASH_ERROR] = 'User creation failed.';
//   }
//   header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//   exit;
// }

function handleCreate()
{
  $name         = trim($_POST['name'] ?? '');
  $email        = trim($_POST['email'] ?? '');
  $level        = $_POST['level'] ?? '';
  $is_team_lead = isset($_POST['is_team_lead']) ? 1 : 0;

  if (empty($name) || empty($email) || empty($level)) {
    $_SESSION[Config::FLASH_ERROR] = 'All fields are required.';
    ajaxRespondAndRedirect(false, 'All fields are required.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid email format.';
    ajaxRespondAndRedirect(false, 'Invalid email format.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if (!in_array($level, Config::USER_LEVELS)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user level.';
    ajaxRespondAndRedirect(false, 'Invalid user level.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Prevent creating Admin users via UI
  if ($level === 'Admin') {
    $_SESSION[Config::FLASH_ERROR] = 'Cannot create Admin users via UI. Admins must be added directly in the database.';
    ajaxRespondAndRedirect(false, 'Cannot create Admin users via UI.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if ($is_team_lead && $level !== 'Senior') {
    $_SESSION[Config::FLASH_ERROR] = 'Only Senior users can be Team Leads.';
    ajaxRespondAndRedirect(false, 'Only Senior users can be Team Leads.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if (User::getByLogin($email)) {
    $_SESSION[Config::FLASH_ERROR] = 'Email already exists.';
    ajaxRespondAndRedirect(false, 'Email already exists.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  $result = User::adminCreate($name, $email, null, $level, $is_team_lead);
  if ($result) {
    $successMessage = 'User created successfully. Random password: ' . $result['random_pw'];
    $_SESSION[Config::FLASH_SUCCESS] = $successMessage;
    ajaxRespondAndRedirect(true, $successMessage);
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'User creation failed.';
    ajaxRespondAndRedirect(false, 'User creation failed.');
  }

  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

function handleUpdate()
{
  $id   = $_POST['id'] ?? 0;
  $data = [];

  if (isset($_POST['name']))  $data['name']  = trim($_POST['name']);
  if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
  if (isset($_POST['level'])) $data['level'] = $_POST['level'];
  $data['is_team_lead'] = isset($_POST['is_team_lead']) ? 1 : 0;

  if (empty($id) || empty($data)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
    ajaxRespondAndRedirect(false, 'Invalid data.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Prevent promoting to Admin
  if (isset($data['level']) && $data['level'] === 'Admin') {
    $_SESSION[Config::FLASH_ERROR] = 'Cannot promote users to Admin via UI. Admins must be added directly in the database.';
    ajaxRespondAndRedirect(false, 'Cannot promote users to Admin via UI.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Normalize Team Lead flag
  $level = $data['level'] ?? null;

  if (!$level) {
    $existingUser = User::getById($id);
    $level = $existingUser ? $existingUser->getLevel() : null;
  }

  // FORCE rule: only Seniors can be Team Leads
  if ($level !== 'Senior') {
    $data['is_team_lead'] = 0;
  }


  if (User::update($id, $data)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User updated successfully.';
    ajaxRespondAndRedirect(true, 'User updated successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'User update failed.';
    ajaxRespondAndRedirect(false, 'User update failed.');
  }

  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

// function handleApprove()
// {
//   $id = $_POST['id'] ?? 0;

//   if (empty($id)) {
//     $_SESSION[Config::FLASH_ERROR] = 'Invalid user ID.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   if (User::approve($id)) {
//     $_SESSION[Config::FLASH_SUCCESS] = 'User approved successfully.';
//   } else {
//     $_SESSION[Config::FLASH_ERROR] = 'User approval failed.';
//   }
//   header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//   exit;
// }

function handleApprove()
{
  $id = $_POST['id'] ?? 0;

  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user ID.';
    ajaxRespondAndRedirect(false, 'Invalid user ID.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if (User::approve($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User approved successfully.';
    ajaxRespondAndRedirect(true, 'User approved successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'User approval failed.';
    ajaxRespondAndRedirect(false, 'User approval failed.');
  }

  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

// function handleDelete()
// {
//   $id = $_POST['id'] ?? 0;

//   // Prevent deleting yourself
//   if (empty($id) || $id == $_SESSION['user_id']) {
//     $_SESSION[Config::FLASH_ERROR] = 'Cannot delete yourself.';
//     header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//     exit;
//   }

//   // Get user to delete
//   $userToDelete = User::getById($id);

//   // Check if user is an Admin
//   if ($userToDelete && $userToDelete->getLevel() === 'Admin') {
//     // Count how many admins exist
//     $allUsers = User::getAll();
//     $adminCount = 0;

//     foreach ($allUsers as $userData) {
//       // Handle both array and object formats
//       if (is_array($userData) && isset($userData['level']) && $userData['level'] === 'Admin') {
//         $adminCount++;
//       } elseif (is_object($userData) && $userData->getLevel() === 'Admin') {
//         $adminCount++;
//       }
//     }

//     // Prevent deleting the last admin
//     if ($adminCount <= 1) {
//       $_SESSION[Config::FLASH_ERROR] = 'Cannot delete the last admin.';
//       header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//       exit;
//     }
//   }

//   if (User::delete($id)) {
//     $_SESSION[Config::FLASH_SUCCESS] = 'User deleted successfully.';
//   } else {
//     $_SESSION[Config::FLASH_ERROR] = 'Failed to delete user.';
//   }
//   header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
//   exit;
// }

function handleDelete()
{
  $id = $_POST['id'] ?? 0;

  // Prevent deleting yourself
  if (empty($id) || $id == $_SESSION['user_id']) {
    $_SESSION[Config::FLASH_ERROR] = 'Cannot delete yourself.';
    ajaxRespondAndRedirect(false, 'Cannot delete yourself.');
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Get user to delete
  $userToDelete = User::getById($id);

  // Check if user is an Admin
  if ($userToDelete && $userToDelete->getLevel() === 'Admin') {
    // Count how many admins exist
    $allUsers = User::getAll();
    $adminCount = 0;

    foreach ($allUsers as $userData) {
      // Handle both array and object formats
      if (is_array($userData) && isset($userData['level']) && $userData['level'] === 'Admin') {
        $adminCount++;
      } elseif (is_object($userData) && $userData->getLevel() === 'Admin') {
        $adminCount++;
      }
    }

    // Prevent deleting the last admin
    if ($adminCount <= 1) {
      $_SESSION[Config::FLASH_ERROR] = 'Cannot delete the last admin.';
      ajaxRespondAndRedirect(false, 'Cannot delete the last admin.');
      header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
      exit;
    }
  }

  if (User::delete($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User deleted successfully.';
    ajaxRespondAndRedirect(true, 'User deleted successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Failed to delete user.';
    ajaxRespondAndRedirect(false, 'Failed to delete user.');
  }

  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}
