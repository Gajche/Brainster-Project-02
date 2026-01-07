<?php

require_once __DIR__ . '/../../autoload.php';

// Auth check
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

$userId = $_SESSION['user_id'];
$userLevel = $_SESSION['user_level'] ?? 'User';
$isTeamLead = $_SESSION['is_team_lead'] ?? false;

// User title
$userTitle = $userLevel;
if ($userLevel === 'Senior' && $isTeamLead) {
  $userTitle = 'Team Lead Senior';
}

// Fetch projects using DatabaseHelper (automatically handles exceptions)
$projects = DatabaseHelper::safeGetProjects(isAdmin() ? null : $userId);

// Pass data to view
$_SESSION['dashboard_data'] = [
  'user_name' => $_SESSION['user_name'] ?? 'User',
  'user_email' => $_SESSION['user_email'] ?? 'N/A',
  'user_title' => $userTitle,
  'user_id' => $userId,
  'is_admin' => isAdmin(),
  'projects' => $projects
];
