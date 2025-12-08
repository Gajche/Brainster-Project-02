<?php

require_once __DIR__ . '/../includes/config.php';  // Relative path to load Config class first
// require_once Config::ROOT_DIR . '/includes/config.php';
require_once Config::ROOT_DIR . '/models/Database.php';
require_once Config::ROOT_DIR . '/models/User.php';

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
    // List or invalid
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
}

// Functions
function handleResetPassword()
{
  $id = $_POST['id'] ?? 0;
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user ID.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  $newPw = User::adminResetPassword($id);
  if ($newPw) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Password reset. New password: ' . $newPw;
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Password reset failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

function handleCreate()
{
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $level = $_POST['level'] ?? '';
  $is_team_lead = isset($_POST['is_team_lead']) ? 1 : 0;

  if (empty($name) || empty($email) || empty($level)) {
    $_SESSION[Config::FLASH_ERROR] = 'All fields are required.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid email format.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if (!in_array($level, Config::USER_LEVELS)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user level.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if ($is_team_lead && $level !== 'Senior') {
    $_SESSION[Config::FLASH_ERROR] = 'Only Senior users can be Team Leads.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Check email unique
  if (User::getByLogin($email)) {
    $_SESSION[Config::FLASH_ERROR] = 'Email already exists.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  $result = User::adminCreate($name, $email, null, $level, $is_team_lead);
  if ($result) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User created. Random password: ' . $result['random_pw'];
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Creation failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

function handleUpdate()
{
  $id = $_POST['id'] ?? 0;
  $data = [];
  if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
  if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
  if (isset($_POST['level'])) $data['level'] = $_POST['level'];
  $data['is_team_lead'] = isset($_POST['is_team_lead']) ? 1 : 0;

  if (empty($id) || empty($data)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  // Ensure only Seniors can be team leads
  if ($data['is_team_lead']) {
    $level = $data['level'] ?? '';
    if (empty($level)) {
      $user = User::getById($id);
      $level = $user ? $user->getLevel()
        : '';
    }
    if ($level !== 'Senior') {
      $_SESSION[Config::FLASH_ERROR] = 'Only Senior users can be Team Leads.';
      header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
      exit;
    }
  }

  if (User::update($id, $data)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User updated.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Update failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

function handleApprove()
{
  $id = $_POST['id'] ?? 0;
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user ID.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if (User::approve($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User approved.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Approval failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}

function handleDelete()
{
  $id = $_POST['id'] ?? 0;
  if (empty($id) || $id == $_SESSION['user_id']) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid or self-delete.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
    exit;
  }

  if (User::delete($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'User deleted.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Delete failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_users');
  exit;
}
