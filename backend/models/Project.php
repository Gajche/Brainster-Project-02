<?php

require_once __DIR__ . '/../../autoload.php';

// require_once __DIR__ . '/../includes/config.php';  
// require_once Config::ROOT_DIR . '/includes/config.php';
// require_once Config::ROOT_DIR . '/models/Database.php';
// require_once Config::ROOT_DIR . '/models/User.php';  

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
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    return $data ? new self($data) : null;
  }

  // Get all projects
  public static function getAll()
  {
    $db = Database::getInstance();
    $stmt = $db->query('SELECT * FROM projects');
    $projects = [];
    while ($data = $stmt->fetch()) {
      $projects[] = new self($data);
    }
    return $projects;
  }

  // Get projects for a user (led or member)
  public static function getForUser($userId)
  {
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
  }

  // Create project (Admin only)
  public static function create($data)
  {
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
  }

  // Update project (Admin)
  public static function update($id, $data)
  {
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
  }

  // Delete project (Admin)
  public static function delete($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('DELETE FROM projects WHERE id = ?');
    return $stmt->execute([$id]);
  }

  // Add member to team (Team Lead only)
  public static function addMember($projectId, $userId)
  {
    // Prevent adding an Admin to a project team
    $userToAdd = User::getById($userId);
    if ($userToAdd && $userToAdd->getLevel() === 'Admin') {
      return false;
    }

    $db = Database::getInstance();
    $stmt = $db->prepare('INSERT IGNORE INTO project_users (project_id, user_id) VALUES (?, ?)');
    return $stmt->execute([$projectId, $userId]);
  }

  // Remove member from team (Team Lead only, can't remove self if lead)
  public static function removeMember($projectId, $userId)
  {
    $project = self::getById($projectId);
    if ($project && $project->getTeamLeadId() == $userId) {
      return false;  // Can't remove lead
    }
    $db = Database::getInstance();
    $stmt = $db->prepare('DELETE FROM project_users WHERE project_id = ? AND user_id = ?');
    return $stmt->execute([$projectId, $userId]);
  }

  // Mark project as Done (Team Lead, if all tasks Done)
  public static function markDone($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT COUNT(*) FROM tasks WHERE project_id = ? AND status != "Done"');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 0) {
      $stmt = $db->prepare('UPDATE projects SET status = "Done" WHERE id = ?');
      return $stmt->execute([$id]);
    }
    return false;
  }

  // Permission checks
  public function canManageTeam($userId)
  {
    $user = User::getById($userId);
    return ($user && $user->getLevel() === 'Admin') || ($this->team_lead_id == $userId);
  }

  public function canAccess($userId)
  {
    $user = User::getById($userId);
    if ($user && $user->getLevel() === 'Admin') {
      return true;
    }
    return $this->team_lead_id == $userId || User::isInProject($userId, $this->id);
  }

  // Get tasks for project
  public function getTasks()
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT * FROM tasks WHERE project_id = ?');
    $stmt->execute([$this->id]);
    return $stmt->fetchAll();
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
