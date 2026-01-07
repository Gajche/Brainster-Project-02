<?php

require_once __DIR__ . '/../../autoload.php';

class Project
{
  private $id;
  private $title;
  private $description;
  private $requirements;
  private $estimated_time;
  private $deadline;
  private $team_lead_id;
  private $status;

  public function __construct($data)
  {
    $this->id = $data['id'] ?? null;
    $this->title = $data['title'] ?? '';
    $this->description = $data['description'] ?? '';
    $this->requirements = $data['requirements'] ?? '';
    $this->estimated_time = $data['estimated_time'] ?? '';
    $this->deadline = $data['deadline'] ?? null;
    $this->team_lead_id = $data['team_lead_id'] ?? null;
    $this->status = $data['status'] ?? 'Active';
  }

  // Get project by ID
  public static function getById($id)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
      $stmt->execute([$id]);
      $data = $stmt->fetch();
      return $data ? new self($data) : null;
    } catch (PDOException $e) {
      error_log('[PROJECT getById] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Get all projects
  public static function getAll()
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->query('SELECT * FROM projects');
      $projects = [];
      while ($data = $stmt->fetch()) {
        $projects[] = new self($data);
      }
      return $projects;
    } catch (PDOException $e) {
      error_log('[PROJECT getAll] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Get projects for a user (led or member)
  public static function getForUser($userId)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('
              SELECT p.* FROM projects p
              LEFT JOIN project_users pu ON p.id = pu.project_id
              WHERE p.team_lead_id = ? OR pu.user_id = ?
              GROUP BY p.id
          ');
      $stmt->execute([$userId, $userId]);
      $projects = [];
      while ($data = $stmt->fetch()) {
        $projects[] = new self($data);
      }
      return $projects;
    } catch (PDOException $e) {
      error_log('[PROJECT getForUser] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Create project (Admin only)
  public static function create($data)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('
              INSERT INTO projects (title, description, requirements, estimated_time, deadline, team_lead_id)
              VALUES (?, ?, ?, ?, ?, ?)
          ');
      $exec = $stmt->execute([
        $data['title'],
        $data['description'],
        $data['requirements'],
        $data['estimated_time'],
        $data['deadline'],
        $data['team_lead_id']
      ]);
      if ($exec) {
        $projectId = $db->lastInsertId();
        // Auto-add Team Lead to team
        self::addMember($projectId, $data['team_lead_id']);
        return $projectId;
      }
      return false;
    } catch (PDOException $e) {
      error_log('[PROJECT create] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Update project (Admin)
  public static function update($id, $data)
  {
    try {
      $db = Database::getInstance();
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
      if (isset($data['requirements'])) {
        $fields[] = 'requirements = ?';
        $params[] = $data['requirements'];
      }
      if (isset($data['estimated_time'])) {
        $fields[] = 'estimated_time = ?';
        $params[] = $data['estimated_time'];
      }
      if (isset($data['deadline'])) {
        $fields[] = 'deadline = ?';
        $params[] = $data['deadline'];
      }
      if (isset($data['team_lead_id'])) {
        $fields[] = 'team_lead_id = ?';
        $params[] = $data['team_lead_id'];
      }
      if (!empty($fields)) {
        $sql = 'UPDATE projects SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $params[] = $id;
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
      }
      return false;
    } catch (PDOException $e) {
      error_log('[PROJECT update] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Delete project (Admin)
  public static function delete($id)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('DELETE FROM projects WHERE id = ?');
      return $stmt->execute([$id]);
    } catch (PDOException $e) {
      error_log('[PROJECT delete] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Add member to team (Team Lead only)
  public static function addMember($projectId, $userId)
  {
    try {
      // Prevent adding an Admin to a project team
      // User::getById might throw DatabaseException 
      $userToAdd = User::getById($userId);
      if ($userToAdd && $userToAdd->getLevel() === 'Admin') {
        return false;
      }

      $db = Database::getInstance();
      $stmt = $db->prepare('INSERT IGNORE INTO project_users (project_id, user_id) VALUES (?, ?)');
      return $stmt->execute([$projectId, $userId]);
    } catch (PDOException $e) {
      error_log('[PROJECT addMember] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Remove member from team (Team Lead only, can't remove self if lead)
  public static function removeMember($projectId, $userId)
  {
    try {
      // Project::getById might throw DatabaseException 
      $project = self::getById($projectId);
      if ($project && $project->getTeamLeadId() == $userId) {
        return false;  // Can't remove lead
      }
      $db = Database::getInstance();
      $stmt = $db->prepare('DELETE FROM project_users WHERE project_id = ? AND user_id = ?');
      return $stmt->execute([$projectId, $userId]);
    } catch (PDOException $e) {
      error_log('[PROJECT removeMember] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Mark project as Done (Team Lead, if all tasks Done)
  public static function markDone($id)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT COUNT(*) FROM tasks WHERE project_id = ? AND status != "Done"');
      $stmt->execute([$id]);
      if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare('UPDATE projects SET status = "Done" WHERE id = ?');
        return $stmt->execute([$id]);
      }
      return false;
    } catch (PDOException $e) {
      error_log('[PROJECT markDone] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Permission checks
  public function canManageTeam($userId)
  {
    try {
      // User::getById might throw DatabaseException.
      $user = User::getById($userId);
      return ($user && $user->getLevel() === 'Admin') || ($this->team_lead_id == $userId);
    } catch (DatabaseException $e) {
      // If we can't get user info, return false for safety
      error_log('[PROJECT canManageTeam] Failed to get user info: ' . $e->getMessage());
      return false;
    }
  }

  public function canAccess($userId)
  {
    try {
      // User::getById might throw DatabaseException.
      $user = User::getById($userId);
      if ($user && $user->getLevel() === 'Admin') {
        return true;
      }
      // User::isInProject might throw DatabaseException.
      return $this->team_lead_id == $userId || User::isInProject($userId, $this->id);
    } catch (DatabaseException $e) {
      // If we can't verify access, return false for safety
      error_log('[PROJECT canAccess] Failed to verify access: ' . $e->getMessage());
      return false;
    }
  }

  // Get tasks for project
  public function getTasks()
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT * FROM tasks WHERE project_id = ?');
      $stmt->execute([$this->id]);
      return $stmt->fetchAll();
    } catch (PDOException $e) {
      error_log('[PROJECT getTasks] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Getters
  public function getId()
  {
    return $this->id;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function getRequirements()
  {
    return $this->requirements;
  }

  public function getEstimatedTime()
  {
    return $this->estimated_time;
  }

  public function getDeadline()
  {
    return $this->deadline;
  }

  public function getTeamLeadId()
  {
    return $this->team_lead_id;
  }

  public function getStatus()
  {
    return $this->status;
  }
}
