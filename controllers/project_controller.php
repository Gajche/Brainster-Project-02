<?php

require_once __DIR__ . '/../includes/config.php';  // Relative path to load Config class first
// require_once Config::ROOT_DIR . '/includes/config.php';
require_once Config::ROOT_DIR . '/models/Database.php';
require_once Config::ROOT_DIR . '/models/User.php';
require_once Config::ROOT_DIR . '/models/Project.php';

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Ensure logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

$userId = $_SESSION['user_id'];
$userLevel = $_SESSION['user_level'];

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
  case 'create':
    if ($userLevel !== 'Admin') {
      $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
      header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
      exit;
    }
    handleCreate();
    break;
  case 'update':
    if ($userLevel !== 'Admin') {
      $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
      header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
      exit;
    }
    handleUpdate();
    break;
  case 'delete':
    if ($userLevel !== 'Admin') {
      $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
      header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
      exit;
    }
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

// Functions

function handleCreate()
{
  $data = [
    'title' => trim($_POST['title'] ?? ''),
    'description' => $_POST['description'] ?? '',
    'requirements' => $_POST['requirements'] ?? '',
    'estimated_time' => $_POST['estimated_time'] ?? '',
    'deadline' => $_POST['deadline'] ?? '',
    'team_lead_id' => $_POST['team_lead_id'] ?? 0
  ];

  if (empty($data['title']) || empty($data['team_lead_id'])) {
    $_SESSION[Config::FLASH_ERROR] = 'Title and Team Lead required.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
    exit;
  }

  // Check Team Lead is Senior with is_team_lead=1
  $lead = User::getById($data['team_lead_id']);
  if (!$lead || $lead->getLevel() !== 'Senior' || !$lead->isTeamLead()) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid Team Lead.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
    exit;
  }

  $projectId = Project::create($data);
  if ($projectId) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project created.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Creation failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
  exit;
}

function handleUpdate()
{
  $id = $_POST['id'] ?? 0;
  $data = [];
  if (isset($_POST['title'])) $data['title'] = trim($_POST['title']);
  if (isset($_POST['description'])) $data['description'] = $_POST['description'];
  if (isset($_POST['requirements'])) $data['requirements'] = $_POST['requirements'];
  if (isset($_POST['estimated_time'])) $data['estimated_time'] = $_POST['estimated_time'];
  if (isset($_POST['deadline'])) $data['deadline'] = $_POST['deadline'];
  if (isset($_POST['team_lead_id'])) $data['team_lead_id'] = $_POST['team_lead_id'];

  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
    exit;
  }

  if (Project::update($id, $data)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project updated.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Update failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
  exit;
}

function handleDelete()
{
  $id = $_POST['id'] ?? 0;
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
    exit;
  }

  if (Project::delete($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project deleted.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Delete failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=admin_projects');
  exit;
}

function handleAddMember($userId)
{
  $projectId = $_POST['project_id'] ?? 0;
  $memberId = $_POST['member_id'] ?? 0;

  if (empty($projectId) || empty($memberId)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $projectId);
    exit;
  }

  $project = Project::getById($projectId);
  if (!$project || !$project->canManageTeam($userId)) {
    $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $projectId);
    exit;
  }

  if (Project::addMember($projectId, $memberId)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Member added.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Add failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $projectId);
  exit;
}

function handleRemoveMember($userId)
{
  $projectId = $_POST['project_id'] ?? 0;
  $memberId = $_POST['member_id'] ?? 0;

  if (empty($projectId) || empty($memberId)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid data.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $projectId);
    exit;
  }

  $project = Project::getById($projectId);
  if (!$project || !$project->canManageTeam($userId)) {
    $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $projectId);
    exit;
  }

  if (Project::removeMember($projectId, $memberId)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Member removed.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Remove failed.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $projectId);
  exit;
}

function handleMarkDone($userId)
{
  $id = $_POST['id'] ?? 0;
  if (empty($id)) {
    $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $id);
    exit;
  }

  $project = Project::getById($id);
  if (!$project || !$project->canManageTeam($userId)) {
    $_SESSION[Config::FLASH_ERROR] = 'Access denied.';
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $id);
    exit;
  }

  if (Project::markDone($id)) {
    $_SESSION[Config::FLASH_SUCCESS] = 'Project marked as Done.';
  } else {
    $_SESSION[Config::FLASH_ERROR] = 'Cannot mark Done: Not all tasks complete.';
  }
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=project_view&id=' . $id);
  exit;
}
