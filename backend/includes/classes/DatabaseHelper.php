<?php

class DatabaseHelper
{
  /**
   * Safely get all users (with exception handling)
   */
  public static function safeGetUsers()
  {
    try {
      return User::getAll();
    } catch (DatabaseException $e) {
      self::handleException($e, 'GET_USERS');
      return [];
    }
  }

  /**
   * Safely get projects (with exception handling)
   * @param int|null $userId If null and user is admin, gets all projects. If not admin, gets user's projects
   */
  public static function safeGetProjects($userId = null)
  {
    try {
      if (isAdmin() || $userId === null) {
        return Project::getAll();
      } else {
        return Project::getForUser($userId);
      }
    } catch (DatabaseException $e) {
      self::handleException($e, 'GET_PROJECTS');
      return [];
    }
  }

  /**
   * Safely get project by ID (with exception handling)
   */
  public static function safeGetProjectById($projectId)
  {
    try {
      return Project::getById($projectId);
    } catch (DatabaseException $e) {
      self::handleException($e, 'GET_PROJECT_BY_ID');
      return null;
    }
  }

  /**
   * Safely get project team (with exception handling)
   */
  public static function safeGetProjectTeam($projectId)
  {
    try {
      return User::getProjectTeam($projectId);
    } catch (DatabaseException $e) {
      self::handleException($e, 'GET_PROJECT_TEAM');
      return [];
    }
  }

  /**
   * Safely get all users (with exception handling)
   */
  public static function safeGetAllUsers()
  {
    try {
      return User::getAll();
    } catch (DatabaseException $e) {
      self::handleException($e, 'GET_ALL_USERS');
      return [];
    }
  }

  /**
   * Safely get user by ID (with exception handling)
   */
  public static function safeGetUserById($userId)
  {
    try {
      return User::getById($userId);
    } catch (DatabaseException $e) {
      self::handleException($e, 'GET_USER_BY_ID');
      return null;
    }
  }

  /**
   * Safely get tasks by project (with exception handling)
   */
  public static function safeGetTasksByProject($projectId)
  {
    try {
      return Task::getByProject($projectId);
    } catch (DatabaseException $e) {
      self::handleException($e, 'GET_TASKS_BY_PROJECT');
      return [];
    }
  }

  /**
   * Common exception handling method
   */
  private static function handleException(DatabaseException $e, $context)
  {
    // Set flash error message (only if not already set to avoid overwriting)
    if (!isset($_SESSION[Config::FLASH_ERROR])) {
      $_SESSION[Config::FLASH_ERROR] = "Database error: Connection failed or tables are missing.";
    }

    // Log the error with context
    error_log('[DATABASE HELPER - ' . $context . ' ERROR] ' . $e->getMessage() .
      ' in ' . $e->getFile() . ':' . $e->getLine());
  }

  /**
   * Check if database is available
   */
  public static function isDatabaseAvailable()
  {
    try {
      $db = Database::getInstance();
      return $db->isConnected();
    } catch (DatabaseException $e) {
      return false;
    }
  }
}
