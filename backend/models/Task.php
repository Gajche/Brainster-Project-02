<?php

require_once __DIR__ . '/../../autoload.php';

class Task
{
  private $id;
  private $project_id;
  private $title;
  private $description;
  private $created_at;
  private $status;
  private $assigned_to;
  private $created_by;

  public function __construct($data)
  {
    $this->id = $data['id'] ?? null;
    $this->project_id = $data['project_id'] ?? null;
    $this->title = $data['title'] ?? '';
    $this->description = $data['description'] ?? '';
    $this->created_at = $data['created_at'] ?? null;
    $this->status = $data['status'] ?? 'To Do';
    $this->assigned_to = $data['assigned_to'] ?? null;
    $this->created_by = $data['created_by'] ?? null;
  }

  // Get task by ID
  public static function getById($id)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT * FROM tasks WHERE id = ?');
      $stmt->execute([$id]);
      $data = $stmt->fetch();
      return $data ? new self($data) : null;
    } catch (PDOException $e) {
      error_log('[TASK getById] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Get tasks for a project
  public static function getByProject($projectId)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT * FROM tasks WHERE project_id = ? ORDER BY created_at DESC');
      $stmt->execute([$projectId]);
      $tasks = [];
      while ($data = $stmt->fetch()) {
        $tasks[] = new self($data);
      }
      return $tasks;
    } catch (PDOException $e) {
      error_log('[TASK getByProject] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Create task (Admins, Team Leads, or Seniors in project)
  public static function create($data, $userId)
  {
    try {
      // User::getById() might throw DatabaseException 
      $user = User::getById($userId);
      if (!$user || !$user->canCreateTask($data['project_id'])) {
        return false;
      }

      // Project::getById() might throw DatabaseException 
      $project = Project::getById($data['project_id']);
      if (!$project) {
        return false;
      }

      $db = Database::getInstance();
      $stmt = $db->prepare('INSERT INTO tasks (project_id, title, description, created_by) VALUES (?, ?, ?, ?)');
      if ($stmt->execute([$data['project_id'], $data['title'], $data['description'], $userId])) {
        return $db->lastInsertId();
      }
      return false;
    } catch (PDOException $e) {
      error_log('[TASK create] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Assign task
  public function assign($newAssigneeId, $userId)
  {
    try {
      $db = Database::getInstance();

      // User::getById() might throw DatabaseException 
      $assigningUser = User::getById($userId);
      if (!$assigningUser) {
        return false;
      }

      // Use isAdmin() helper function (checks session)
      if (isAdmin() || User::isProjectLead($userId, $this->project_id)) {
        if ($newAssigneeId !== null && !$assigningUser->canAssignTaskTo($newAssigneeId, $this->project_id)) {
          return false;
        }
        $stmt = $db->prepare('UPDATE tasks SET assigned_to = ? WHERE id = ?');
        return $stmt->execute([$newAssigneeId, $this->id]);
      }

      $currentAssignee = $this->getAssignedTo() ? User::getById($this->getAssignedTo()) : null;

      if ($newAssigneeId == $userId && $currentAssignee === null) {
        if ($assigningUser->canAssignTaskTo($newAssigneeId, $this->project_id)) {
          $stmt = $db->prepare('UPDATE tasks SET assigned_to = ? WHERE id = ?');
          return $stmt->execute([$newAssigneeId, $this->id]);
        }
      }

      $permission = false;
      if ($currentAssignee === null) {
        if ($assigningUser->getLevel() === 'Senior' || $assigningUser->getLevel() === 'Mid') {
          $permission = true;
        }
      } else {
        if ($assigningUser->getLevel() === 'Mid') {
          // Mid can only assign tasks that are:
          // 1. Unassigned (handled above) OR
          // 2. Assigned to themselves OR 
          // 3. Assigned to a Junior
          // CANNOT assign tasks assigned to another Mid user
          if ($currentAssignee->getId() == $assigningUser->getId() || $currentAssignee->getLevel() === 'Junior') {
            $permission = true;
          }
          // Explicitly NO permission for tasks assigned to other Mid users
        }
        if ($assigningUser->getLevel() === 'Senior') {
          if ($currentAssignee->getId() == $assigningUser->getId() || in_array($currentAssignee->getLevel(), ['Mid', 'Junior'])) {
            $permission = true;
          }
        }
      }

      if ($permission && $assigningUser->canAssignTaskTo($newAssigneeId, $this->project_id)) {
        $stmt = $db->prepare('UPDATE tasks SET assigned_to = ? WHERE id = ?');
        return $stmt->execute([$newAssigneeId, $this->id]);
      }

      return false;
    } catch (PDOException $e) {
      error_log('[TASK assign] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Update status
  public function updateStatus($newStatus, $userId)
  {
    try {
      // User::getById() might throw DatabaseException 
      $user = User::getById($userId);
      if (!$user || !$user->canChangeTaskStatus($this->id)) {
        return false;
      }

      $oldStatus = $this->status;

      // Config::TASK_STATUSES should be available since Config is loaded first
      if (!in_array($newStatus, Config::TASK_STATUSES) || $newStatus === $oldStatus) {
        return false;
      }

      $db = Database::getInstance();
      $stmt = $db->prepare('UPDATE tasks SET status = ? WHERE id = ?');
      $exec = $stmt->execute([$newStatus, $this->id]);

      if ($exec) {
        $content = '[' . $user->getName() . '] changed the status from ' . $oldStatus . ' to ' . $newStatus;
        // Comment::create() might throw DatabaseException 
        Comment::create(['task_id' => $this->id, 'user_id' => $userId, 'content' => $content]);
        return true;
      }
      return false;
    } catch (PDOException $e) {
      error_log('[TASK updateStatus] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Update task details
  public function update($data, $userId)
  {
    try {
      // User::getById() might throw DatabaseException 
      $user = User::getById($userId);
      // Project::getById() might throw DatabaseException 
      $project = Project::getById($this->project_id);

      if (!$user || !$project) {
        return false;
      }

      $isAdmin = $user->getLevel() === 'Admin';
      $isTeamLead = $project->canManageTeam($userId);
      $isSeniorInProject = ($user->getLevel() === 'Senior' && User::isInProject($userId, $this->project_id));

      if ($isSeniorInProject && $this->getAssignedTo() == $project->getTeamLeadId()) {
        return false;
      }

      if (!$isAdmin && !$isTeamLead && !$isSeniorInProject) {
        return false;
      }

      $fields = [];
      $params = [];
      if (isset($data['title'])) {
        $fields[] = 'title = ?';
        $params[] = $data['title'];
      }
      if (isset($data['description'])) {
        $fields[] = 'description = ?';
        $params[] = $data['description'];
      }
      if (!empty($fields)) {
        $sql = 'UPDATE tasks SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $params[] = $this->id;
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
      }
      return false;
    } catch (PDOException $e) {
      error_log('[TASK update] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Delete task
  public static function delete($id, $userId)
  {
    try {
      // getById() might throw DatabaseException 
      $task = self::getById($id);
      if (!$task) {
        return false;
      }

      // User::getById() might throw DatabaseException 
      $user = User::getById($userId);
      // Project::getById() might throw DatabaseException 
      $project = Project::getById($task->project_id);

      if (!$user || !$project) {
        return false;
      }

      if ($user->getLevel() !== 'Admin' && $project->getTeamLeadId() != $userId) {
        return false;
      }

      $db = Database::getInstance();
      $stmt = $db->prepare('DELETE FROM tasks WHERE id = ?');
      return $stmt->execute([$id]);
    } catch (PDOException $e) {
      error_log('[TASK delete] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Get comments for task
  public function getComments()
  {
    try {
      // Comment::getByTask() might throw DatabaseException 
      return Comment::getByTask($this->id);
    } catch (DatabaseException $e) {
      // If we can't get comments, return empty array for safety
      error_log('[TASK getComments] Failed to get comments: ' . $e->getMessage());
      return [];
    }
  }

  // New getter for creator
  public function getCreatedBy()
  {
    return $this->created_by;
  }

  // Getters
  public function getId()
  {
    return $this->id;
  }

  public function getProjectId()
  {
    return $this->project_id;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function getCreatedAt()
  {
    return $this->created_at;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function getAssignedTo()
  {
    return $this->assigned_to;
  }
}
