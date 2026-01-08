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


try {
  switch ($action) {
    case 'create':
      requireAdmin();
      handleCreate();
      break;

    case 'update':
      requireAdmin();
      handleUpdate();
      break;

    case 'delete':
      requireAdmin();
      handleDelete();
      break;

    case 'add_member':
      handleAddMember($userId);
      break;

    case 'remove_member':
      handleRemoveMember($userId);
      break;

    case 'mark_done':
      handleMarkDone($userId);
      break;

    default:
      throw new ValidationException('Invalid action.');
  }
} catch (ValidationException $e) {
  // For AJAX requests, use handleResponse
  if (isAjax()) {
    handleResponse(false, $e->getMessage(), null, 400);
  } else {
    // For regular requests, flash and redirect
    $_SESSION[Config::FLASH_ERROR] = $e->getMessage();
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  }
  exit;
} catch (Exception $e) {
  // Catch-all for unexpected errors
  if (isAjax()) {
    handleResponse(false, 'An unexpected error occurred.', null, 500);
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'An unexpected error occurred.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  }
  exit;
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

// Create Project
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
  if (empty($data['title']) || empty($data['description']) || empty($data['requirements']) || empty($data['estimated_time']) || empty($data['deadline']) || empty($data['team_lead_id'])) {
    handleResponse(false, 'All fields required.', null, 400);
    return;
  }

  // Validate Team Lead
  // Verifies if the provided team_lead_id corresponds to a valid 'Senior' level team lead.
  $lead = User::getById($data['team_lead_id']);
  if (!$lead || $lead->getLevel() !== 'Senior' || !$lead->isTeamLead()) {
    handleResponse(false, 'Invalid Team Lead.', null, 400);
    return;
  }

  // Create project
  // Attempts to create a new project in the database using the validated data.
  $projectId = Project::create($data);

  if ($projectId) {
    handleResponse(true, 'Project created successfully.');
  } else {
    handleResponse(false, 'Project creation failed.', null, 500);
  }
}



// Update Project
function handleUpdate()
{
  // ID
  $id = $_POST['id'] ?? null;

  if (!filter_var($id, FILTER_VALIDATE_INT)) {
    handleResponse(false, 'Invalid project ID.', null, 400);
    return;
  }

  // Validation
  $validators = [
    'title'          => fn($v) => trim($v) !== '',
    'description'    => fn($v) => trim($v) !== '',
    'requirements'   => fn($v) => trim($v) !== '',
    'estimated_time' => fn($v) =>
    preg_match('/^\s*\d+\s+(day|days|week|weeks|month|months|year|years)\s*$/i', $v),
    'deadline'       => fn($v) => strtotime($v),
    'team_lead_id'   => fn($v) => filter_var($v, FILTER_VALIDATE_INT),
  ];

  foreach ($validators as $field => $check) {
    if (!isset($_POST[$field]) || !$check($_POST[$field])) {
      handleResponse(false, "Invalid value for: $field", null, 400);
      return;
    }
  }

  // Data
  $data = [
    'title'          => trim($_POST['title']),
    'description'    => trim($_POST['description']),
    'requirements'   => trim($_POST['requirements']),
    'estimated_time' => trim($_POST['estimated_time']),
    'deadline'       => $_POST['deadline'],
    'team_lead_id'   => (int) $_POST['team_lead_id'],
  ];

  // Update
  if (Project::update($id, $data)) {
    handleResponse(true, 'Project updated successfully.');
  } else {
    handleResponse(false, 'Project update failed.', null, 500);
  }
}






function handleDelete()
{
  // Retrieves the project ID from the POST request.
  $id = $_POST['id'] ?? 0;

  // Validates if a project ID was provided.
  if (empty($id)) {
    handleResponse(false, 'Invalid project ID.', null, 400);
    return;
  }

  // Attempts to delete the project with the specified ID.
  if (Project::delete($id)) {
    handleResponse(true, 'Project deleted successfully.');
  } else {
    handleResponse(false, 'Failed to delete project.', null, 500);
  }
}

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
    handleResponse(false, 'Invalid data.', null, 400);
    return;
  }

  // Permission check
  // Retrieves the project and verifies if the current user has rights to manage its team.
  $project = Project::getById($projectId);
  if (!$project || !$project->canManageTeam($userId)) {
    handleResponse(false, 'Access denied.', null, 403);
    return;
  }

  // Add member
  // Attempts to add the specified member to the project.
  if (Project::addMember($projectId, $memberId)) {
    handleResponse(true, 'Member added successfully.');
  } else {
    handleResponse(false, 'Failed to add member.', null, 500);
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
    handleResponse(false, 'Invalid data.', null, 400);
    return;
  }

  // Permission check
  // Retrieves the project and verifies if the current user has rights to manage its team.
  $project = Project::getById($projectId);
  if (!$project || !$project->canManageTeam($userId)) {
    handleResponse(false, 'Access denied.', null, 403);
    return;
  }

  // Remove member
  // Attempts to remove the specified member from the project.
  if (Project::removeMember($projectId, $memberId)) {
    handleResponse(true, 'Member removed successfully.');
  } else {
    handleResponse(false, 'Failed to remove member.', null, 500);
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
    handleResponse(false, 'Invalid project ID.', null, 400);
    return;
  }

  // Permission check
  // Retrieves the project and verifies if the current user has rights to manage its team.
  $project = Project::getById($id);
  if (!$project || !$project->canManageTeam($userId)) {
    handleResponse(false, 'Access denied.', null, 403);
    return;
  }

  // Mark project as done
  // Attempts to mark the project as done. The underlying Project::markDone() method
  // likely contains business logic to check if all project tasks are completed.
  if (Project::markDone($id)) {
    handleResponse(true, 'Project marked as Done.');
  } else {
    // This message implies Project::markDone() has internal validation for task completion.
    handleResponse(false, 'Cannot mark Done: Not all tasks are complete.', null, 400);
  }
}
