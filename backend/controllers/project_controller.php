<?php

require_once __DIR__ . '/../../autoload.php';
// Ensures essential classes and configurations are loaded for the application.

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Ensures a PHP session is active to maintain user state across requests.


// Ensure logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit; // Terminates script execution after redirection.
}

$userId    = $_SESSION['user_id'];
$userLevel = $_SESSION['user_level'];
// Retrieves current user's ID and access level from the session for authorization and operations.


// Handle actions
// Dispatches control to specific handler functions based on the 'action' parameter.
$action = $_POST['action'] ?? $_GET['action'] ?? ''; // Safely retrieves action from POST or GET, defaulting to an empty string.

switch ($action) {
  case 'create':
    requireAdmin(); // Enforces admin-level access for project creation.
    handleCreate();
    break;

  case 'update':
    requireAdmin(); // Enforces admin-level access for project updates.
    handleUpdate();
    break;

  case 'delete':
    requireAdmin(); // Enforces admin-level access for project deletion.
    handleDelete();
    break;

  case 'add_member':
    // Parameter: $userId - The ID of the currently logged-in user, used for permission checks.
    handleAddMember($userId);
    break;

  case 'remove_member':
    // Parameter: $userId - The ID of the currently logged-in user, used for permission checks.
    handleRemoveMember($userId);
    break;

  case 'mark_done':
    // Parameter: $userId - The ID of the currently logged-in user, used for permission checks.
    handleMarkDone($userId);
    break;

  default:
    // Redirects to the dashboard if no valid action is provided.
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
    exit; // Terminates script execution after redirection.
}



// HELPER FUNCTIONS

/**
 * Require admin access or redirect
 */
function requireAdmin()
{
  if ($_SESSION['user_level'] !== 'Admin') {
    $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
    exit; // Stops script execution to prevent further processing after redirection.
  }
}

/**
 * Redirect to a specific page
 */
function redirectToPage($page)
{
  // Parameter: string $page - The target page identifier (e.g., 'dashboard', 'admin_projects').
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=' . $page);
  exit; // Stops script execution to ensure immediate redirection.
}

/**
 * Redirect to project view
 */
function redirectToProject($projectId)
{
  // Parameter: int $projectId - The ID of the project to view.
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $projectId);
  exit; // Stops script execution to ensure immediate redirection.
}

/**
 * Send JSON response if AJAX request, otherwise continue with redirect
 * This function determines if the request is an AJAX request by checking the 'HTTP_X_REQUESTED_WITH' header.
 * If it is an AJAX request, a JSON response is sent, and the script exits.
 * Otherwise, the script continues its execution, typically leading to a subsequent redirect.
 * @param bool $success - Indicates if the operation was successful.
 * @param string $message - A descriptive message for the client.
 */
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
    exit; // Exits script after sending JSON response for AJAX requests.
  }
}


function handleCreate()
{
  // Extracts and sanitizes project data from the POST request.
  $data = [
    'title'           => trim($_POST['title'] ?? ''),
    'description'     => $_POST['description'] ?? '',
    'requirements'    => $_POST['requirements'] ?? '',
    'estimated_time'  => $_POST['estimated_time'] ?? '',
    'deadline'        => $_POST['deadline'] ?? '',
    'team_lead_id'    => $_POST['team_lead_id'] ?? 0
  ];

  // Validation
  // Checks for required fields: title and team_lead_id. If missing, sets error and handles response.
  if (empty($data['title']) || empty($data['team_lead_id'])) {
    $_SESSION[Config::FLASH_ERROR] = 'Title and Team Lead required.';
    ajaxRespondAndRedirect(false, 'Title and Team Lead required.');
    redirectToPage('admin_projects'); // Redirects for non-AJAX requests.
  }

  // Validate Team Lead
  // Verifies if the provided team_lead_id corresponds to a valid 'Senior' level team lead.
  $lead = User::getById($data['team_lead_id']);
  if (!$lead || $lead->getLevel() !== 'Senior' || !$lead->isTeamLead()) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid Team Lead.';
    ajaxRespondAndRedirect(false, 'Invalid Team Lead.');
    redirectToPage('admin_projects'); // Redirects for non-AJAX requests.
  }

  // Create project
  // Attempts to create a new project in the database using the validated data.
  $projectId = Project::create($data);

  if ($projectId) {
    $successMessage = 'Project created successfully.';
    $_SESSION[Config::FLASH_SUCCESS] = $successMessage;
    ajaxRespondAndRedirect(true, $successMessage);
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Project creation failed.';
    ajaxRespondAndRedirect(false, 'Project creation failed.');
  }

  // Final redirect for non-AJAX requests, or if ajaxRespondAndRedirect did not exit.
  redirectToPage('admin_projects');
}


function handleUpdate()
{
  // Retrieves the project ID from the POST request.
  $id = $_POST['id'] ?? 0;

  // Validates if a project ID was provided.
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    ajaxRespondAndRedirect(false, 'Invalid project ID.');
    redirectToPage('admin_projects'); // Redirects for non-AJAX requests.
  }

  // Dynamically builds the data array with only the fields present in the POST request.
  $data = [];
  if (isset($_POST['title']))           $data['title'] = trim($_POST['title']);
  if (isset($_POST['description']))     $data['description'] = $_POST['description'];
  if (isset($_POST['requirements']))    $data['requirements'] = $_POST['requirements'];
  if (isset($_POST['estimated_time']))  $data['estimated_time'] = $_POST['estimated_time'];
  if (isset($_POST['deadline']))        $data['deadline'] = $_POST['deadline'];
  if (isset($_POST['team_lead_id']))    $data['team_lead_id'] = $_POST['team_lead_id'];

  // Attempts to update the project with the provided ID and data.
  if (Project::update($id, $data)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project updated successfully.';
    ajaxRespondAndRedirect(true, 'Project updated successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Project update failed.';
    ajaxRespondAndRedirect(false, 'Project update failed.');
  }

  // Final redirect for non-AJAX requests, or if ajaxRespondAndRedirect did not exit.
  redirectToPage('admin_projects');
}


function handleDelete()
{
  // Retrieves the project ID from the POST request.
  $id = $_POST['id'] ?? 0;

  // Validates if a project ID was provided.
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    ajaxRespondAndRedirect(false, 'Invalid project ID.');
    redirectToPage('admin_projects'); // Redirects for non-AJAX requests.
  }

  // Attempts to delete the project with the specified ID.
  if (Project::delete($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project deleted successfully.';
    ajaxRespondAndRedirect(true, 'Project deleted successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Failed to delete project.';
    ajaxRespondAndRedirect(false, 'Failed to delete project.');
  }

  // Final redirect for non-AJAX requests, or if ajaxRespondAndRedirect did not exit.
  redirectToPage('admin_projects');
}



// PROJECT TEAM MANAGEMENT (Team Lead or Admin)

/**
 * Handles adding a member to a project. Requires current user ($userId) to have management permissions.
 * @param int $userId - The ID of the user attempting to add a member (for permission checking).
 */
function handleAddMember($userId)
{
  $projectId = $_POST['project_id'] ?? 0;
  $memberId  = $_POST['member_id'] ?? 0;

  // Validation
  // Checks if both project ID and member ID are provided.
  if (empty($projectId) || empty($memberId)) {
    // Note: `isAjax()` and `handleResponse()` are assumed helper functions not defined in this snippet.
    if (isAjax()) {
      handleResponse(false, 'Invalid data.', null, 400);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
      redirectToProject($projectId); // Redirects to the project view, even if projectId is 0.
    }
  }

  // Permission check
  // Retrieves the project and verifies if the current user has rights to manage its team.
  $project = Project::getById($projectId);
  if (!$project || !$project->canManageTeam($userId)) {
    if (isAjax()) {
      handleResponse(false, 'Access denied.', null, 403);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
      redirectToProject($projectId); // Redirects to the project view, even if project is not found.
    }
  }

  // Add member
  // Attempts to add the specified member to the project.
  if (Project::addMember($projectId, $memberId)) {
    if (isAjax()) {
      handleResponse(true, 'Member added successfully.');
    } else {
      $_SESSION[Config::FLASH_SUCCESS] = 'Member added successfully.';
      redirectToProject($projectId);
    }
  } else {
    if (isAjax()) {
      handleResponse(false, 'Failed to add member.', null, 500);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Failed to add member.';
      redirectToProject($projectId);
    }
  }
}

/**
 * Handles removing a member from a project. Requires current user ($userId) to have management permissions.
 * @param int $userId - The ID of the user attempting to remove a member (for permission checking).
 */
function handleRemoveMember($userId)
{
  $projectId = $_POST['project_id'] ?? 0;
  $memberId  = $_POST['member_id'] ?? 0;

  // Validation
  // Checks if both project ID and member ID are provided.
  if (empty($projectId) || empty($memberId)) {
    // Note: `isAjax()` and `handleResponse()` are assumed helper functions not defined in this snippet.
    if (isAjax()) {
      handleResponse(false, 'Invalid data.', null, 400);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
      redirectToProject($projectId); // Redirects to the project view, even if projectId is 0.
    }
  }

  // Permission check
  // Retrieves the project and verifies if the current user has rights to manage its team.
  $project = Project::getById($projectId);
  if (!$project || !$project->canManageTeam($userId)) {
    if (isAjax()) {
      handleResponse(false, 'Access denied.', null, 403);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
      redirectToProject($projectId); // Redirects to the project view, even if project is not found.
    }
  }

  // Remove member
  // Attempts to remove the specified member from the project.
  if (Project::removeMember($projectId, $memberId)) {
    if (isAjax()) {
      handleResponse(true, 'Member removed successfully.');
    } else {
      $_SESSION[Config::FLASH_SUCCESS] = 'Member removed successfully.';
      redirectToProject($projectId);
    }
  } else {
    if (isAjax()) {
      handleResponse(false, 'Failed to remove member.', null, 500);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Failed to remove member.';
      redirectToProject($projectId);
    }
  }
}

/**
 * Handles marking a project as done. Requires current user ($userId) to have management permissions.
 * @param int $userId - The ID of the user attempting to mark the project as done (for permission checking).
 */
function handleMarkDone($userId)
{
  // Retrieves the project ID from the POST request.
  $id = $_POST['id'] ?? 0;

  // Validates if a project ID was provided.
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    // Potential issue: Redirecting with an invalid ID (0) may lead to an incorrect or error page.
    redirectToProject($id);
  }

  // Permission check
  // Retrieves the project and verifies if the current user has rights to manage its team.
  $project = Project::getById($id);
  if (!$project || !$project->canManageTeam($userId)) {
    $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
    // Potential issue: Redirecting with an invalid ID (0) may lead to an incorrect or error page.
    redirectToProject($id);
  }

  // Mark project as done
  // Attempts to mark the project as done. The underlying Project::markDone() method
  // likely contains business logic to check if all project tasks are completed.
  if (Project::markDone($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project marked as Done.';
  } else {
    // This message implies Project::markDone() has internal validation for task completion.
    $_SESSION[Config::FLASH_ERROR] = 'Cannot mark Done: Not all tasks are complete.';
  }

  // Final redirect to the project view.
  redirectToProject($id);
}