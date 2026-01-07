<?php

require_once __DIR__ . '/../../autoload.php';

// Auth check
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

$projectId = $_GET['id'] ?? 0;
if (!$projectId) {
  $_SESSION[Config::FLASH_ERROR] = 'Invalid project ID.';
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

// Get project safely using DatabaseHelper
$project = DatabaseHelper::safeGetProjectById($projectId);

// Check if project was fetched (or null due to database error)
if (!$project) {
  if (!isset($_SESSION[Config::FLASH_ERROR])) {
    $_SESSION[Config::FLASH_ERROR] = 'Project not found.';
  }

  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

$userId = $_SESSION['user_id'];

// Check access permissions
if (!$project->canAccess($userId)) {
  $_SESSION[Config::FLASH_ERROR] = 'Project not found or access denied.';
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

// Get project status info
$statusInfo = ProjectHelper::getViewStatusInfo($project);
$isProjectOverdue = strpos($statusInfo['badge'], 'Overdue') !== false;

// Get team and users safely using DatabaseHelper
$team = DatabaseHelper::safeGetProjectTeam($projectId);
$allUsers = DatabaseHelper::safeGetAllUsers();

// Filter available members (excluding team members and admins)
$availableMembers = array_filter($allUsers, function ($user) use ($team) {
  // Check if user is in team
  $inTeam = false;
  foreach ($team as $teamMember) {
    if (isset($teamMember['id']) && $teamMember['id'] == $user['id']) {
      $inTeam = true;
      break;
    }
  }

  // Return true if not in team and not admin
  return !$inTeam && (!isset($user['level']) || $user['level'] !== 'Admin');
});

// Get tasks safely
$tasks = DatabaseHelper::safeGetTasksByProject($projectId);

// Fallback to 'To Do' if status null/invalid
$tasksByStatus = [];
foreach (Config::TASK_STATUSES as $status) {
  $tasksByStatus[$status] = [];
}

foreach ($tasks as $task) {
  $status = $task->getStatus() ?: 'To Do';
  if (!in_array($status, Config::TASK_STATUSES)) {
    $status = 'To Do'; // Extra safety for invalid statuses
  }
  $tasksByStatus[$status][] = $task;
}

// Get current user safely
$currentUser = DatabaseHelper::safeGetUserById($userId);

// Check if current user exists and can create task
$canCreateTask = false;
$assignableUsers = [];

if ($currentUser) {
  $canCreateTask = $currentUser->canCreateTask($projectId);
  if ($canCreateTask) {
    try {
      $assignableUsers = $currentUser->getAssignableUsers($projectId);
    } catch (DatabaseException $e) {
      // If this fails, assignableUsers remains empty
      error_log('[GET ASSIGNABLE USERS ERROR] ' . $e->getMessage());
    }
  }
}

$isTeamLead = $project->canManageTeam($userId);

$_SESSION['project_view_data'] = [
  'project' => $project,
  'statusInfo' => $statusInfo,
  'isProjectOverdue' => $isProjectOverdue,
  'team' => $team,
  'availableMembers' => $availableMembers,
  'tasksByStatus' => $tasksByStatus,
  'isTeamLead' => $isTeamLead,
  'canCreateTask' => $canCreateTask,
  'assignableUsers' => $assignableUsers,
  'projectId' => $projectId,
  'userId' => $userId
];
