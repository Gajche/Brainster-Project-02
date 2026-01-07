<?php

// Ensure logged in
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

require_once __DIR__ . '/../../../autoload.php';

// Load task & user
$taskId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];
$userLevel = $_SESSION['user_level'] ?? null;

$task = Task::getById($taskId);
$user = User::getById($userId);

// Access control
if (!$task || (!isAdmin() && !$user->isInProject($userId, $task->getProjectId()))) {
  $_SESSION[Config::FLASH_ERROR] = 'Task not found or access denied.';
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

// Related data
$assigned = $task->getAssignedTo()
  ? User::getById($task->getAssignedTo())
  : null;

$assignedName = $assigned
  ? htmlspecialchars($assigned->getName())
  : 'Unassigned';

$comments = $task->getComments();

// Permissions 
$canComment      = $user->canCommentOnTask($taskId);
$canChangeStatus = $user->canChangeTaskStatus($taskId);

$isTeamLead = User::isProjectLead($userId, $task->getProjectId());

$canAssign = isAdmin()
  || $isTeamLead
  || !empty($user->getAssignableUsers($task->getProjectId()));

$canEditTask =
  $userLevel === 'Admin' ||
  ($userLevel === 'Senior' && User::isInProject($userId, $task->getProjectId()));
