<?php

// Load from project root (two levels up from backend/controllers/)
require_once __DIR__ . '/../../autoload.php';

// Session start
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
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
      throw new ValidationException('Invalid action.');
  }
} catch (ValidationException $e) {
  $_SESSION[Config::FLASH_ERROR] = $e->getMessage();
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
} catch (AuthenticationException $e) {
  $_SESSION[Config::FLASH_ERROR] = $e->getMessage();
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
} catch (Exception $e) {
  $_SESSION[Config::FLASH_ERROR] = 'An unexpected error occurred.';
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}


// REGISTER
function handleRegister()
{
  $name           = trim($_POST['name'] ?? '');
  $email          = trim($_POST['email'] ?? '');
  $password       = $_POST['password'] ?? '';
  $repeatPassword = $_POST['repeat_password'] ?? '';
  $level          = $_POST['level'] ?? '';

  // Validate using Validator class
  try {
    Validator::validate([
      'name' => ['required'],
      'email' => ['required', 'email'],
      'password' => ['required', 'password' => 6],
      'level' => ['required', 'userLevel'],
    ], compact('name', 'email', 'password', 'level'));

    // Additional validation
    if (!Validator::passwordsMatch($password, $repeatPassword)) {
      throw new ValidationException('Passwords do not match.');
    }

    if (User::getByLogin($email)) {
      throw new ValidationException('Email already registered.');
    }
  } catch (ValidationException $e) {
    $_SESSION[Config::FLASH_ERROR] = $e->getMessage();
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit;
  }

  // Sanitize name
  $name = Validator::sanitize($name);

  // Register user
  if (User::register($name, $email, $password, $level)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Registration successful! Await admin approval.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Registration failed. Please try again.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
  }
  exit;
}


// LOGIN (supports both normal and AJAX login)
function handleLogin()
{
  // Rate limiting
  $maxAttempts = 5;
  $lockoutTime = 15 * 60; // 15 minutes

  if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout_time'] = null;
  }

  // Check if locked out
  if ($_SESSION['login_lockout_time'] && time() < $_SESSION['login_lockout_time']) {
    $remaining = ceil(($_SESSION['login_lockout_time'] - time()) / 60);
    throw new AuthenticationException("Too many failed attempts. Try again in {$remaining} minutes.");
  }

  $login    = trim($_POST['login'] ?? '');
  $password = $_POST['password'] ?? '';

  // Validate inputs
  if (!Validator::required($login) || !Validator::required($password)) {
    if (isAjax()) {
      apiError('bad_request', 'All fields are required.', 400);
    }
    throw new ValidationException('All fields are required.');
  }

  $user = User::getByLogin($login);

  if (!$user || !$user->isApproved()) {
    if (isAjax()) {
      apiError('unauthorized', 'User not found or not approved.', 401);
    }
    throw new AuthenticationException('User not found or not approved.');
  }

  if (password_verify($password, $user->getPassword())) {
    // Successful login - set session
    $_SESSION['user_id']       = $user->getId();
    $_SESSION['user_name']     = $user->getName();
    $_SESSION['user_email']    = $user->getEmail();
    $_SESSION['user_level']    = $user->getLevel();
    $_SESSION['is_team_lead']  = $user->isTeamLead();

    if (isAjax()) {
      apiSuccess(
        ['redirect' => Config::getBaseUrl() . 'index.php?page=dashboard'],
        'Login successful'
      );
    }

    $_SESSION[Config::FLASH_SUCCESS] = 'Login successful!';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
    exit;
  }

  // On failed login
  $_SESSION['login_attempts']++;
  if ($_SESSION['login_attempts'] >= $maxAttempts) {
    $_SESSION['login_lockout_time'] = time() + $lockoutTime;
    throw new AuthenticationException('Too many failed attempts. Account locked for 15 minutes.');
  }

  // On successful login
  $_SESSION['login_attempts'] = 0;
  $_SESSION['login_lockout_time'] = null;

  // Invalid password
  if (isAjax()) {
    apiError('unauthorized', 'Invalid password.', 401);
  }
  throw new AuthenticationException('Invalid password.');
}


// LOGOUT
function handleLogout()
{
  session_destroy();
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}


// CHANGE PASSWORD
function handleChangePassword()
{
  if (!isset($_SESSION['user_id'])) {
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    exit;
  }

  $oldPw        = $_POST['old_password'] ?? '';
  $newPw        = $_POST['new_password'] ?? '';
  $repeatNewPw  = $_POST['repeat_new_password'] ?? '';

  // Validate inputs
  try {
    Validator::validate([
      'old_password' => ['required'],
      'new_password' => ['required', 'password' => 6],
    ], ['old_password' => $oldPw, 'new_password' => $newPw]);

    if (!Validator::passwordsMatch($newPw, $repeatNewPw)) {
      throw new ValidationException('New passwords do not match.');
    }
  } catch (ValidationException $e) {
    $_SESSION[Config::FLASH_ERROR] = $e->getMessage();

    if (isAjax()) {
      apiError('bad_request', $e->getMessage(), 400);
    }

    header('Location: ' . Config::getBaseUrl() . 'index.php?page=change_password');
    exit;
  }

  $user = User::getById($_SESSION['user_id']);

  if ($user->changePassword($oldPw, $newPw)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Password changed successfully!';

    if (isAjax()) {
      apiSuccess(null, 'Password changed successfully!');
    }

    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
    exit;
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Old password incorrect.';

    if (isAjax()) {
      apiError('unauthorized', 'Old password incorrect.', 401);
    }

    header('Location: ' . Config::getBaseUrl() . 'index.php?page=change_password');
    exit;
  }
}
