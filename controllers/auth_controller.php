<?php

require_once __DIR__ . '/../autoload.php';
// require_once __DIR__ . '/../includes/config.php';
// require_once __DIR__ . '/../includes/api.php';
// require_once Config::ROOT_DIR . '/models/Database.php';
// require_once Config::ROOT_DIR . '/models/User.php';

// Session start
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Handle actions
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
    $_SESSION[Config::FLASH_ERROR] = 'Invalid action.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// REGISTER
// ─────────────────────────────────────────────────────────────────────────────
function handleRegister()
{
  $name           = trim($_POST['name'] ?? '');
  $email          = trim($_POST['email'] ?? '');
  $password       = $_POST['password'] ?? '';
  $repeatPassword = $_POST['repeat_password'] ?? '';
  $level          = $_POST['level'] ?? '';

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

  // New: Enhanced email regex validation (more strict than filter_var)
  if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid email format.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  // Sanitize name (prevents special chars/XSS)
  $name = htmlspecialchars($name); // Sanitize specials

  // Optional: Add password length check for strength (spec implies edge cases)
  if (strlen($password) < 6) {
    $_SESSION[Config::FLASH_ERROR] = 'Password must be at least 6 characters.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  if (!in_array($level, Config::USER_LEVELS) || $level === 'Admin') {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid user level.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  if (User::getByLogin($email)) {
    $_SESSION[Config::FLASH_ERROR] = 'Email already registered.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  if (User::register($name, $email, $password, $level)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Registration successful! Await admin approval.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Registration failed. Please try again.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
  }
  exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// LOGIN (supports both normal and AJAX login)
// ─────────────────────────────────────────────────────────────────────────────
function handleLogin()
{
  $login    = trim($_POST['login'] ?? '');
  $password = $_POST['password'] ?? '';
  $is_ajax  = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

  if (empty($login) || empty($password)) {
    $is_ajax
      ? apiError('bad_request', 'All fields are required.', 400)
      : ($_SESSION[Config::FLASH_ERROR] = 'All fields are required.');
    $is_ajax ? null : header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    $is_ajax ? null : exit;
  }

  $user = User::getByLogin($login);

  if (!$user || !$user->isApproved()) {
    $is_ajax
      ? apiError('unauthorized', 'User not found or not approved.', 401)
      : ($_SESSION[Config::FLASH_ERROR] = 'User not found or not approved.');
    $is_ajax ? null : header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    $is_ajax ? null : exit;
  }

  if (password_verify($password, $user->getPassword())) {
    // Successful login — set session
    $_SESSION['user_id']       = $user->getId();
    $_SESSION['user_name']     = $user->getName();
    $_SESSION['user_email']    = $user->getEmail();
    $_SESSION['user_level']    = $user->getLevel();
    $_SESSION['is_team_lead']  = $user->isTeamLead();

    if ($is_ajax) {
      apiSuccess(
        ['redirect' => Config::getBaseUrl() . 'index.php?page=dashboard'],
        'Login successful'
      );
    }

    $_SESSION[Config::FLASH_SUCCESS] = 'Login successful!';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
    exit;
  }

  // Invalid password
  $is_ajax
    ? apiError('unauthorized', 'Invalid password.', 401)
    : ($_SESSION[Config::FLASH_ERROR] = 'Invalid password.');

  $is_ajax ? null : header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  $is_ajax ? null : exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// LOGOUT
// ─────────────────────────────────────────────────────────────────────────────
function handleLogout()
{
  session_destroy();
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// CHANGE PASSWORD
// ─────────────────────────────────────────────────────────────────────────────
function handleChangePassword()
{
  if (!isset($_SESSION['user_id'])) {
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    exit;
  }

  $oldPw        = $_POST['old_password'] ?? '';
  $newPw        = $_POST['new_password'] ?? '';
  $repeatNewPw  = $_POST['repeat_new_password'] ?? '';

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
