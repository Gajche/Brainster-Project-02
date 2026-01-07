<?php

require_once __DIR__ . '/../../autoload.php';

// Ensure only Admin
if (!isAdmin()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

// Fetch projects safely using DatabaseHelper
$projects = DatabaseHelper::safeGetProjects(); // For admin, gets all projects

// Fetch eligible Team Leads (Seniors with is_team_lead=1)
$allUsers = DatabaseHelper::safeGetAllUsers();
$teamLeads = [];

if (!empty($allUsers)) {
  $teamLeads = array_filter($allUsers, function ($user) {
    // Check if user is Senior and team lead
    $isSenior = isset($user['level']) && $user['level'] === 'Senior';
    $isTeamLead = isset($user['is_team_lead']) && $user['is_team_lead'] == 1;
    return $isSenior && $isTeamLead;
  });
}

// Pass to view
$_SESSION['admin_projects_data'] = [
  'projects' => $projects,
  'teamLeads' => $teamLeads
];
