<?php

require_once __DIR__ . '/../includes/config.php';  // Relative path to load Config class first
// require_once Config::ROOT_DIR . '/includes/config.php';
require_once Config::ROOT_DIR . '/models/Database.php';
require_once Config::ROOT_DIR . '/models/User.php';

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Handle actions based on $_GET['action'] or direct POST
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
  case 'register':
    handleRegister();
    break;
  case 'login':
    handleLogin();
    break;
  case 'logout':
    handleLogout();
    break;
  case 'change_password':
    handleChangePassword();
    break;
  default:

    // Invalid action
    $_SESSION[Config::FLASH_ERROR] = 'Invalid action.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    exit;
}

// Functions

function handleRegister()
{
  // Validate inputs
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $repeatPassword = $_POST['repeat_password'] ?? '';
  $level = $_POST['level'] ?? '';

  if (empty($name) || empty($email) || empty($password) || empty($level)) {
    $_SESSION[Config::FLASH_ERROR] = 'All fields are required.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  if ($password !== $repeatPassword) {
    $_SESSION[Config::FLASH_ERROR] = 'Passwords do not match.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid email format.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  if (!in_array($level, Config::USER_LEVELS) || $level === 'Admin') {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user level.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  // Check if email exists
  $existingUser = User::getByLogin($email);
  if ($existingUser) {
    $_SESSION[Config::FLASH_ERROR] = 'Email already registered.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  if (User::register($name, $email, $password, $level)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Registration successful! Await admin approval.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Registration failed.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
  }
  exit;
}

function handleLogin()
{
  $login = trim($_POST['login'] ?? '');
  $password = $_POST['password'] ?? '';
  $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

  if (empty($login) || empty($password)) {
    if ($is_ajax) {
      header('Content-Type: application/json');
      http_response_code(400); // Bad Request
      echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
      exit;
    }
    $_SESSION[Config::FLASH_ERROR] = 'All fields are required.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    exit;
  }

  $user = User::getByLogin($login);
  if (!$user || !$user->isApproved()) {
    if ($is_ajax) {
      header('Content-Type: application/json');
      http_response_code(401); // Unauthorized
      echo json_encode(['status' => 'error', 'message' => 'User not found or not approved.']);
      exit;
    }
    $_SESSION[Config::FLASH_ERROR] = 'User not found or not approved.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    exit;
  }

  if (password_verify($password, $user->getPassword())) {
    $_SESSION['user_id'] = $user->getId();
    $_SESSION['user_name'] = $user->getName();
    $_SESSION['user_email'] = $user->getEmail();
    $_SESSION['user_level'] = $user->getLevel();
    $_SESSION['is_team_lead'] = $user->isTeamLead();

    if ($is_ajax) {
      header('Content-Type: application/json');
      echo json_encode(['status' => 'success', 'redirect' => Config::getBaseUrl() . 'index.php?page=dashboard']);
      exit;
    }
    $_SESSION[Config::FLASH_SUCCESS] = 'Login successful!';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  } else {
    if ($is_ajax) {
      header('Content-Type: application/json');
      http_response_code(401); // Unauthorized
      echo json_encode(['status' => 'error', 'message' => 'Invalid password.']);
      exit;
    }
    $_SESSION[Config::FLASH_ERROR] = 'Invalid password.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  }
  exit;
}

function handleLogout()
{
  session_destroy();
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

function handleChangePassword()
{
  if (!isset($_SESSION['user_id'])) {
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    exit;
  }

  $oldPw = $_POST['old_password'] ?? '';
  $newPw = $_POST['new_password'] ?? '';
  $repeatNewPw = $_POST['repeat_new_password'] ?? '';

  if (empty($oldPw) || empty($newPw) || empty($repeatNewPw)) {
    $_SESSION[Config::FLASH_ERROR] = 'All fields are required.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=change_password');
    exit;
  }

  if ($newPw !== $repeatNewPw) {
    $_SESSION[Config::FLASH_ERROR] = 'New passwords do not match.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=change_password');
    exit;
  }

  $user = User::getById($_SESSION['user_id']);
  if ($user->changePassword($oldPw, $newPw)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Password changed successfully!';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Old password incorrect.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=change_password');
  }
  exit;
}
