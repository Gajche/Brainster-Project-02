<?php

require_once __DIR__ . '/../../autoload.php';

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}


// Ensure logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

$userId    = $_SESSION['user_id'];
$userLevel = $_SESSION['user_level'];



// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

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
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
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
    exit;
  }
}

/**
 * Redirect to a specific page
 */
function redirectToPage($page)
{
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=' . $page);
  exit;
}

/**
 * Redirect to project view
 */
function redirectToProject($projectId)
{
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $projectId);
  exit;
}

/**
 * Send JSON response if AJAX request, otherwise continue with redirect
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
    exit;
  }
}




// ADMIN PROJECT ACTIONS (Admin only)

// function handleCreate()
// {
//   $data = [
//     'title'           => trim($_POST['title'] ?? ''),
//     'description'     => $_POST['description'] ?? '',
//     'requirements'    => $_POST['requirements'] ?? '',
//     'estimated_time'  => $_POST['estimated_time'] ?? '',
//     'deadline'        => $_POST['deadline'] ?? '',
//     'team_lead_id'    => $_POST['team_lead_id'] ?? 0
//   ];

//   // Validation
//   if (empty($data['title']) || empty($data['team_lead_id'])) {
//     $_SESSION[Config::FLASH_ERROR] = 'Title and Team Lead required.';
//     redirectToPage('admin_projects');
//   }

//   // Validate Team Lead
//   $lead = User::getById($data['team_lead_id']);
//   if (!$lead || $lead->getLevel() !== 'Senior' || !$lead->isTeamLead()) {
//     $_SESSION[Config::FLASH_ERROR] = 'Invalid Team Lead.';
//     redirectToPage('admin_projects');
//   }

//   // Create project
//   $projectId = Project::create($data);

//   if ($projectId) {
//     $_SESSION[Config::FLASH_SUCCESS] = 'Project created successfully.';
//   } else {
//     $_SESSION[Config::FLASH_ERROR] = 'Project creation failed.';
//   }

//   redirectToPage('admin_projects');
// }

function handleCreate()
{
  $data = [
    'title'           => trim($_POST['title'] ?? ''),
    'description'     => $_POST['description'] ?? '',
    'requirements'    => $_POST['requirements'] ?? '',
    'estimated_time'  => $_POST['estimated_time'] ?? '',
    'deadline'        => $_POST['deadline'] ?? '',
    'team_lead_id'    => $_POST['team_lead_id'] ?? 0
  ];

  // Validation
  if (empty($data['title']) || empty($data['team_lead_id'])) {
    $_SESSION[Config::FLASH_ERROR] = 'Title and Team Lead required.';
    ajaxRespondAndRedirect(false, 'Title and Team Lead required.');
    redirectToPage('admin_projects');
  }

  // Validate Team Lead
  $lead = User::getById($data['team_lead_id']);
  if (!$lead || $lead->getLevel() !== 'Senior' || !$lead->isTeamLead()) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid Team Lead.';
    ajaxRespondAndRedirect(false, 'Invalid Team Lead.');
    redirectToPage('admin_projects');
  }

  // Create project
  $projectId = Project::create($data);

  if ($projectId) {
    $successMessage = 'Project created successfully.';
    $_SESSION[Config::FLASH_SUCCESS] = $successMessage;
    ajaxRespondAndRedirect(true, $successMessage);
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Project creation failed.';
    ajaxRespondAndRedirect(false, 'Project creation failed.');
  }

  redirectToPage('admin_projects');
}

// function handleUpdate()
// {
//   $id = $_POST['id'] ?? 0;

//   if (empty($id)) {
//     $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
//     redirectToPage('admin_projects');
//   }

//   $data = [];
//   if (isset($_POST['title']))           $data['title']           = trim($_POST['title']);
//   if (isset($_POST['description']))     $data['description']     = $_POST['description'];
//   if (isset($_POST['requirements']))    $data['requirements']    = $_POST['requirements'];
//   if (isset($_POST['estimated_time']))  $data['estimated_time']  = $_POST['estimated_time'];
//   if (isset($_POST['deadline']))        $data['deadline']        = $_POST['deadline'];
//   if (isset($_POST['team_lead_id']))    $data['team_lead_id']    = $_POST['team_lead_id'];

//   if (Project::update($id, $data)) {
//     $_SESSION[Config::FLASH_SUCCESS] = 'Project updated successfully.';
//   } else {
//     $_SESSION[Config::FLASH_ERROR] = 'Project update failed.';
//   }

//   redirectToPage('admin_projects');
// }

function handleUpdate()
{
  $id = $_POST['id'] ?? 0;

  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    ajaxRespondAndRedirect(false, 'Invalid project ID.');
    redirectToPage('admin_projects');
  }

  $data = [];
  if (isset($_POST['title']))           $data['title'] = trim($_POST['title']);
  if (isset($_POST['description']))     $data['description'] = $_POST['description'];
  if (isset($_POST['requirements']))    $data['requirements'] = $_POST['requirements'];
  if (isset($_POST['estimated_time']))  $data['estimated_time'] = $_POST['estimated_time'];
  if (isset($_POST['deadline']))        $data['deadline'] = $_POST['deadline'];
  if (isset($_POST['team_lead_id']))    $data['team_lead_id'] = $_POST['team_lead_id'];

  if (Project::update($id, $data)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project updated successfully.';
    ajaxRespondAndRedirect(true, 'Project updated successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Project update failed.';
    ajaxRespondAndRedirect(false, 'Project update failed.');
  }

  redirectToPage('admin_projects');
}

// function handleDelete()
// {
//   $id = $_POST['id'] ?? 0;

//   if (empty($id)) {
//     $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
//     redirectToPage('admin_projects');
//   }

//   if (Project::delete($id)) {
//     $_SESSION[Config::FLASH_SUCCESS] = 'Project deleted successfully.';
//   } else {
//     $_SESSION[Config::FLASH_ERROR] = 'Failed to delete project.';
//   }

//   redirectToPage('admin_projects');
// }

function handleDelete()
{
  $id = $_POST['id'] ?? 0;

  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    ajaxRespondAndRedirect(false, 'Invalid project ID.');
    redirectToPage('admin_projects');
  }

  if (Project::delete($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project deleted successfully.';
    ajaxRespondAndRedirect(true, 'Project deleted successfully.');
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Failed to delete project.';
    ajaxRespondAndRedirect(false, 'Failed to delete project.');
  }

  redirectToPage('admin_projects');
}



// PROJECT TEAM MANAGEMENT (Team Lead or Admin)

function handleAddMember($userId)
{
  $projectId = $_POST['project_id'] ?? 0;
  $memberId  = $_POST['member_id'] ?? 0;

  // Validation
  if (empty($projectId) || empty($memberId)) {
    if (isAjax()) {
      handleResponse(false, 'Invalid data.', null, 400);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
      redirectToProject($projectId);
    }
  }

  // Permission check
  $project = Project::getById($projectId);
  if (!$project || !$project->canManageTeam($userId)) {
    if (isAjax()) {
      handleResponse(false, 'Access denied.', null, 403);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
      redirectToProject($projectId);
    }
  }

  // Add member
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

function handleRemoveMember($userId)
{
  $projectId = $_POST['project_id'] ?? 0;
  $memberId  = $_POST['member_id'] ?? 0;

  // Validation
  if (empty($projectId) || empty($memberId)) {
    if (isAjax()) {
      handleResponse(false, 'Invalid data.', null, 400);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
      redirectToProject($projectId);
    }
  }

  // Permission check
  $project = Project::getById($projectId);
  if (!$project || !$project->canManageTeam($userId)) {
    if (isAjax()) {
      handleResponse(false, 'Access denied.', null, 403);
    } else {
      $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
      redirectToProject($projectId);
    }
  }

  // Remove member
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

function handleMarkDone($userId)
{
  $id = $_POST['id'] ?? 0;

  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    redirectToProject($id);
  }

  $project = Project::getById($id);
  if (!$project || !$project->canManageTeam($userId)) {
    $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
    redirectToProject($id);
  }

  if (Project::markDone($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project marked as Done.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Cannot mark Done: Not all tasks are complete.';
  }

  redirectToProject($id);
}
