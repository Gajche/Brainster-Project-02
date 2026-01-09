<?php
// backend/controllers/admin_projects_controller.php

require_once __DIR__ . '/../../autoload.php';

// Ensure only Admin
if (!isAdmin()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

try {
  // Fetch projects safely using DatabaseHelper
  $projects = DatabaseHelper::safeGetProjects();

  // Fetch eligible Team Leads
  $allUsers = DatabaseHelper::safeGetAllUsers();
  $teamLeads = [];

  if (!empty($allUsers)) {
    $teamLeads = array_filter($allUsers, function ($user) {
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
} catch (DatabaseException $e) {
  // Set user-friendly error message
  $_SESSION[Config::FLASH_ERROR] = 'Unable to load projects. Please ensure the database is initialized and XAMPP (MySQL & Apache) is running.';

  // Provide empty data to prevent view errors
  $_SESSION['admin_projects_data'] = [
    'projects' => [],
    'teamLeads' => []
  ];

  // Log the actual error for debugging
  error_log("Database error in admin_projects_controller: " . $e->getMessage());
}
