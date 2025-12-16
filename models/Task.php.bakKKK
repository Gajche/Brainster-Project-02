<?php

// 🎯 ONLY ONE LINE NEEDED - autoload handles the rest!
require_once __DIR__ . '/../autoload.php';

class Task
{
  private $id;
  private $project_id;
  private $title;
  private $description;
  private $created_at;
  private $status;
  private $assigned_to;

  public function __construct($data)
  {
    $this->id = $data['id'] ?? null;
    $this->project_id = $data['project_id'] ?? null;
    $this->title = $data['title'] ?? '';
    $this->description = $data['description'] ?? '';
    $this->created_at = $data['created_at'] ?? null;
    $this->status = $data['status'] ?? 'To Do';
    $this->assigned_to = $data['assigned_to'] ?? null;
  }

  // Get task by ID
  public static function getById($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT * FROM tasks WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    return $data ? new self($data) : null;
  }

  // Get tasks for a project
  public static function getByProject($projectId)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT * FROM tasks WHERE project_id = ? ORDER BY created_at DESC');
    $stmt->execute([$projectId]);
    $tasks = [];
    while ($data = $stmt->fetch()) {
      $tasks[] = new self($data);
    }
    return $tasks;
  }

  // Create task (Admins, Team Leads, or Seniors in project)
  public static function create($data, $userId)
  {
    $user = User::getById($userId);
    if (!$user || !$user->canCreateTask($data['project_id'])) {
      return false;
    }

    $project = Project::getById($data['project_id']);
    if (!$project) {
      return false;
    }

    $db = Database::getInstance();
    $stmt = $db->prepare('INSERT INTO tasks (project_id, title, description) VALUES (?, ?, ?)');
    if ($stmt->execute([$data['project_id'], $data['title'], $data['description']])) {
      return $db->lastInsertId();
    }
    return false;
  }

  // Assign task
  public function assign($newAssigneeId, $userId)
  {
    $db = Database::getInstance();
    $assigningUser = User::getById($userId);
    if (!$assigningUser) {
      return false;
    }

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
        if ($currentAssignee->getId() == $assigningUser->getId() || $currentAssignee->getLevel() === 'Junior') {
          $permission = true;
        }
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
  }

  // Update status
  public function updateStatus($newStatus, $userId)
  {
    $user = User::getById($userId);
    if (!$user || !$user->canChangeTaskStatus($this->id)) {
      return false;
    }
    $oldStatus = $this->status;
    if (!in_array($newStatus, Config::TASK_STATUSES) || $newStatus === $oldStatus) {
      return false;
    }
    $db = Database::getInstance();
    $stmt = $db->prepare('UPDATE tasks SET status = ? WHERE id = ?');
    $exec = $stmt->execute([$newStatus, $this->id]);
    if ($exec) {
      $content = '[' . $user->getName() . '] changed the status from ' . $oldStatus . ' to ' . $newStatus;
      Comment::create(['task_id' => $this->id, 'user_id' => $userId, 'content' => $content]);
      return true;
    }
    return false;
  }

  // Update task details
  public function update($data, $userId)
  {
    $user = User::getById($userId);
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
  }

  // Delete task
  public static function delete($id, $userId)
  {
    $task = self::getById($id);
    if (!$task) {
      return false;
    }

    $user = User::getById($userId);
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
  }

  // Get comments for task
  public function getComments()
  {
    return Comment::getByTask($this->id);
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
