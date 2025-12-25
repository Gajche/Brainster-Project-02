<?php

// Load from project root (two levels up from backend/controllers/)
require_once __DIR__ . '/../../autoload.php';
// Ensures all necessary classes are available via the autoloader.

// Session start
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Initializes the session if one has not already been started, allowing access to $_SESSION superglobal.

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';
// Determines the requested action from POST, then GET, defaulting to an empty string if neither is set.

try {
  switch ($action) {
    case 'register':
      // Invokes the user registration logic.
      handleRegister();
      break;
    case 'login':
      // Invokes the user login logic, supporting both standard and AJAX requests.
      handleLogin();
      break;
    case 'logout':
      // Invokes the user logout logic, terminating the session.
      handleLogout();
      break;
    case 'change_password':
      // Invokes the logic for changing a user's password.
      handleChangePassword();
      break;
    default:
      // Throws an exception for any unrecognized or unsupported action, preventing further processing.
      throw new ValidationException('Invalid action.');
  }
} catch (ValidationException $e) {
  // Catches validation-specific errors, typically client-side input issues.
  $_SESSION[Config::FLASH_ERROR] = $e->getMessage();
  // Stores the validation error message in the session for display on the login page.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit; // Terminates script execution after redirection.
} catch (AuthenticationException $e) {
  // Catches authentication-specific errors, such as invalid credentials or locked accounts.
  $_SESSION[Config::FLASH_ERROR] = $e->getMessage();
  // Stores the authentication error message in the session for display on the login page.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit; // Terminates script execution after redirection.
} catch (Exception $e) {
  // Catches any other unexpected runtime errors.
  $_SESSION[Config::FLASH_ERROR] = 'An unexpected error occurred.';
  // Provides a generic error message for unexpected failures.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit; // Terminates script execution after redirection.
}


// REGISTER
/**
 * Handles user registration requests.
 * Validates input, checks for existing users, and registers a new user.
 * Upon success or failure, redirects the user with a flash message.
 */
function handleRegister()
{
  // Retrieves and sanitizes registration input from POST.
  $name           = trim($_POST['name'] ?? '');
  $email          = trim($_POST['email'] ?? '');
  $password       = $_POST['password'] ?? '';
  $repeatPassword = $_POST['repeat_password'] ?? '';
  $level          = $_POST['level'] ?? '';

  // Validate using Validator class
  try {
    // Performs initial validation on required fields and formats using the Validator utility.
    Validator::validate([
      'name' => ['required'],
      'email' => ['required', 'email'],
      'password' => ['required', 'password' => 6], // Specifies minimum password length of 6.
      'level' => ['required', 'userLevel'], // Validates the user level against predefined rules.
    ], compact('name', 'email', 'password', 'level'));

    // Additional validation
    // Verifies that the primary password and repeat password fields match.
    if (!Validator::passwordsMatch($password, $repeatPassword)) {
      throw new ValidationException('Passwords do not match.');
    }

    // Checks if a user with the provided email already exists in the system.
    if (User::getByLogin($email)) {
      throw new ValidationException('Email already registered.');
    }
  } catch (ValidationException $e) {
    // Catches validation exceptions during the registration process.
    $_SESSION[Config::FLASH_ERROR] = $e->getMessage();
    // Stores the error message to be displayed on the registration page.
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
    exit; // Stops script execution and redirects.
  }

  // Sanitize name
  // Sanitizes the user's name to prevent XSS or other injection attacks.
  $name = Validator::sanitize($name);

  // Register user
  // Attempts to register the new user with the validated and sanitized data.
  // The new user typically requires admin approval as per the success message.
  if (User::register($name, $email, $password, $level)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Registration successful! Await admin approval.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Registration failed. Please try again.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=register');
  }
  exit; // Ensures no further code is executed after redirection.
}


// LOGIN (supports both normal and AJAX login)
/**
 * Handles user login requests, supporting both traditional form submissions and AJAX calls.
 * Implements rate limiting to mitigate brute-force attacks.
 */
function handleLogin()
{
  // Rate limiting
  // Rate limiting parameters.
  $maxAttempts = 5; // Maximum number of failed login attempts before lockout.
  $lockoutTime = 15 * 60; // Lockout duration in seconds (15 minutes).

  // Initializes session variables for tracking login attempts and lockout status if they don't exist.
  if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout_time'] = null;
  }

  // Check if locked out
  // Verifies if the user is currently under a login lockout period.
  if ($_SESSION['login_lockout_time'] && time() < $_SESSION['login_lockout_time']) {
    $remaining = ceil(($_SESSION['login_lockout_time'] - time()) / 60);
    // Throws an exception if the user is locked out, indicating the remaining time.
    throw new AuthenticationException("Too many failed attempts. Try again in {$remaining} minutes.");
  }

  // Retrieves login credentials from POST data.
  $login    = trim($_POST['login'] ?? '');
  $password = $_POST['password'] ?? '';

  // Validate inputs
  // Performs basic validation to ensure both login and password fields are provided.
  if (!Validator::required($login) || !Validator::required($password)) {
    // If it's an AJAX request, sends a JSON error response.
    if (isAjax()) {
      apiError('bad_request', 'All fields are required.', 400);
    }
    // For non-AJAX requests, throws a validation exception.
    throw new ValidationException('All fields are required.');
  }

  // Attempts to retrieve the user by their login identifier (e.g., email).
  $user = User::getByLogin($login);

  // Checks if the user exists and if their account has been approved.
  if (!$user || !$user->isApproved()) {
    // If user not found or not approved, sends an AJAX error or throws an exception.
    if (isAjax()) {
      apiError('unauthorized', 'User not found or not approved.', 401);
    }
    throw new AuthenticationException('User not found or not approved.');
  }

  // Verifies the provided password against the stored hashed password.
  if (password_verify($password, $user->getPassword())) {
    // Successful login - set session
    // Resets login attempts and lockout status on successful login.
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout_time'] = null;

    // Populates session variables with user details for authentication and authorization.
    $_SESSION['user_id']       = $user->getId();
    $_SESSION['user_name']     = $user->getName();
    $_SESSION['user_email']    = $user->getEmail();
    $_SESSION['user_level']    = $user->getLevel();
    $_SESSION['is_team_lead']  = $user->isTeamLead(); // Indicates if the user holds a team lead role.

    // If AJAX request, returns a success JSON response with a redirect URL.
    if (isAjax()) {
      apiSuccess(
        ['redirect' => Config::getBaseUrl() . 'index.php?page=dashboard'],
        'Login successful'
      );
    }

    // For traditional form submission, sets a flash message and redirects to the dashboard.
    $_SESSION[Config::FLASH_SUCCESS] = 'Login successful!';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
    exit; // Terminates script execution after redirection.
  }

  // On failed login
  // Increments the failed login attempt counter.
  $_SESSION['login_attempts']++;
  // If maximum attempts reached, locks out the account.
  if ($_SESSION['login_attempts'] >= $maxAttempts) {
    $_SESSION['login_lockout_time'] = time() + $lockoutTime;
    // Throws an authentication exception indicating lockout.
    throw new AuthenticationException('Too many failed attempts. Account locked for 15 minutes.');
  }

  // On successful login
  // The following block of code is unreachable.
  // It appears to be misplaced, as 'login_attempts' and 'login_lockout_time' are reset
  // immediately upon successful password verification earlier in the function.
  // $_SESSION['login_attempts'] = 0;
  // $_SESSION['login_lockout_time'] = null;

  // Invalid password
  // If the password verification fails and the account is not yet locked out.
  if (isAjax()) {
    apiError('unauthorized', 'Invalid password.', 401);
  }
  throw new AuthenticationException('Invalid password.');
}


// LOGOUT
/**
 * Handles user logout requests.
 * Destroys the current session and redirects the user to the login page.
 */
function handleLogout()
{
  session_destroy(); // Terminates the user's session, removing all session data.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login'); // Redirects to the login page.
  exit; // Ensures no further code is executed after redirection.
}


// CHANGE PASSWORD
/**
 * Handles requests for changing a user's password.
 * Requires the user to be logged in. Validates old and new passwords.
 * Supports both traditional form submissions and AJAX requests.
 */
function handleChangePassword()
{
  // Verifies that a user is currently logged in.
  if (!isset($_SESSION['user_id'])) {
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
    exit; // Redirects to login if no user is authenticated.
  }

  // Retrieves old and new password inputs from POST data.
  $oldPw        = $_POST['old_password'] ?? '';
  $newPw        = $_POST['new_password'] ?? '';
  $repeatNewPw  = $_POST['repeat_new_password'] ?? '';

  // Validate inputs
  try {
    // Validates the presence of old and new passwords, and minimum length for the new password.
    Validator::validate([
      'old_password' => ['required'],
      'new_password' => ['required', 'password' => 6], // New password must be at least 6 characters.
    ], ['old_password' => $oldPw, 'new_password' => $newPw]);

    // Checks if the new password and its repetition match.
    if (!Validator::passwordsMatch($newPw, $repeatNewPw)) {
      throw new ValidationException('New passwords do not match.');
    }
  } catch (ValidationException $e) {
    // Catches validation errors during password change.
    $_SESSION[Config::FLASH_ERROR] = $e->getMessage();

    // If it's an AJAX request, sends a JSON error response.
    if (isAjax()) {
      apiError('bad_request', $e->getMessage(), 400);
    }

    // For non-AJAX requests, redirects back to the change password page with an error.
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=change_password');
    exit; // Terminates script execution after redirection.
  }

  // Retrieves the current user object based on the session user ID.
  $user = User::getById($_SESSION['user_id']);

  // Attempts to change the user's password.
  // The 'changePassword' method is expected to verify the 'oldPw' against the stored hash.
  if ($user->changePassword($oldPw, $newPw)) {
    // On successful password change.
    $_SESSION[Config::FLASH_SUCCESS] = 'Password changed successfully!';

    // If AJAX, sends a success JSON response.
    if (isAjax()) {
      apiSuccess(null, 'Password changed successfully!');
    }

    // For non-AJAX, redirects to the dashboard.
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
    exit; // Terminates script execution.
  } else {
    // If the old password provided was incorrect.
    $_SESSION[Config::FLASH_ERROR] = 'Old password incorrect.';

    // If AJAX, sends an unauthorized error response.
    if (isAjax()) {
      apiError('unauthorized', 'Old password incorrect.', 401);
    }

    // For non-AJAX, redirects back to the change password page with an error.
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=change_password');
    exit; // Terminates script execution.
  }
}
