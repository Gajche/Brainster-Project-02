<?php

require_once __DIR__ . '/../includes/config.php';  // Relative path to load Config class first
// require_once Config::ROOT_DIR . '/includes/config.php';
require_once Config::ROOT_DIR . '/models/Database.php';
require_once Config::ROOT_DIR . '/models/Task.php';
require_once Config::ROOT_DIR . '/models/Comment.php';

class User
{
  private $id;
  private $name;
  private $email;
  private $level;
  private $is_team_lead;
  private $is_approved;

  // Constructor for instances
  public function __construct($data)
  {
    $this->id = $data['id'] ?? null;
    $this->name = $data['name'] ?? '';
    $this->email = $data['email'] ?? '';
    $this->level = $data['level'] ?? '';
    $this->is_team_lead = $data['is_team_lead'] ?? 0;
    $this->is_approved = $data['is_approved'] ?? 0;
  }

  // Get user by ID
  public static function getById($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    return $data ? new self($data) : null;
  }

  // Get user by email or name for login
  public static function getByLogin($login)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ? OR name = ?');
    $stmt->execute([$login, $login]);
    $data = $stmt->fetch();
    return $data ? new self($data) : null;
  }

  // Register new user (self-reg, pending approval)
  public static function register($name, $email, $password, $level)
  {
    $db = Database::getInstance();
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (name, email, password, level) VALUES (?, ?, ?, ?)');
    return $stmt->execute([$name, $email, $hashed, $level]);
  }

  // Admin create user (auto-approved, random pw)
  public static function adminCreate($name, $email, $password = null, $level, $is_team_lead = 0)
  {
    $db = Database::getInstance();
    $randomPw = $password ?? bin2hex(random_bytes(8));  // Use provided password or generate random
    $hashed = password_hash($randomPw, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (name, email, password, level, is_team_lead, is_approved) VALUES (?, ?, ?, ?, ?, 1)');
    $exec = $stmt->execute([$name, $email, $hashed, $level, $is_team_lead]);
    return $exec ? ['id' => $db->lastInsertId(), 'random_pw' => $randomPw] : false;
  }

  // Admin reset password
  public static function adminResetPassword($userId)
  {
    $db = Database::getInstance();
    $randomPw = bin2hex(random_bytes(8));
    $hashed = password_hash($randomPw, PASSWORD_DEFAULT);
    $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
    if ($stmt->execute([$hashed, $userId])) {
      return $randomPw;
    }
    return false;
  }

  // Approve user (Admin only)
  public static function approve($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('UPDATE users SET is_approved = 1 WHERE id = ?');
    return $stmt->execute([$id]);
  }

  // Change password
  public function changePassword($oldPw, $newPw)
  {
    $db = Database::getInstance();
    $user = self::getById($this->id);
    if (password_verify($oldPw, $user->getPassword())) {
      $hashed = password_hash($newPw, PASSWORD_DEFAULT);
      $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
      return $stmt->execute([$hashed, $this->id]);
    }
    return false;
  }

  // Get all users (for Admin or dropdowns)
  public static function getAll()
  {
    $db = Database::getInstance();
    $stmt = $db->query('SELECT * FROM users');
    return $stmt->fetchAll();
  }

  // Update user (Admin)
  public static function update($id, $data)
  {
    $db = Database::getInstance();
    $fields = [];
    $params = [];
    if (isset($data['name'])) {
      $fields[] = 'name = ?';
      $params[] = $data['name'];
    }
    if (isset($data['email'])) {
      $fields[] = 'email = ?';
      $params[] = $data['email'];
    }
    if (isset($data['level'])) {
      $fields[] = 'level = ?';
      $params[] = $data['level'];
    }
    if (isset($data['is_team_lead'])) {
      $fields[] = 'is_team_lead = ?';
      $params[] = $data['is_team_lead'];
    }
    if (!empty($fields)) {
      $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
      $params[] = $id;
      $stmt = $db->prepare($sql);
      return $stmt->execute($params);
    }
    return false;
  }

  // Delete user (Admin)
  public static function delete($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
    return $stmt->execute([$id]);
  }

  // Permission checks based on spec
  public function canCreateTask($projectId)
  {
    if ($this->level === 'Admin') {
      return true; // Admin can create tasks in any project
    }
    // Team Leads and Seniors in the project can create tasks.
    if ($this->level === 'Senior') {
      return self::isInProject($this->id, $projectId);
    }
    return false;
  }

  public function canAssignTaskTo($assigneeId, $projectId = null)
  {
    // If assigneeId is null, it means we are unassigning the task.
    // The permission to unassign is checked in the Task model based on the *current* assignee.
    // So, if we get this far with a null ID, the action is permitted.
    if ($assigneeId === null) {
      return true;
    }

    $assignee = self::getById($assigneeId);
    if (!$assignee) return false;

    $assigneeLevel = $assignee->getLevel();
    $assigneeIsTeamLead = $assignee->isTeamLead();

    switch ($this->level) {
      case 'Admin':
        return true; // Admin can assign to anyone

      case 'Senior':
        if (self::isProjectLead($this->id, $projectId)) {
          // Team Lead can assign to anyone in the project's team.
          return self::isInProject($assigneeId, $projectId);
        } else {
          // Regular Senior can assign to self, Mid, and Junior. Cannot assign to other Seniors.
          if ($this->id == $assigneeId) {
            return true; // Can assign to self
          }
          return in_array($assigneeLevel, ['Mid', 'Junior']);
        }

      case 'Mid':
        // Mid user can assign to self and Junior. Cannot assign to other Mid users.
        if ($this->id == $assigneeId) {
          return true; // Can assign to self
        }
        return $assigneeLevel === 'Junior';

      case 'Junior':
        // Junior user cannot assign tasks to anyone, including self
        return false;

      default:
        return false;
    }
  }

  public function canChangeTaskStatus($taskId)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT assigned_to, project_id FROM tasks WHERE id = ?');
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) return false;

    $assignedTo = $task['assigned_to'];
    $projectId = $task['project_id'];

    switch ($this->level) {
      case 'Admin':
        return true; // Admin can always change status
      case 'Senior':
        // Seniors can change status of any task in their project
        return self::isInProject($this->id, $projectId);

      case 'Mid':
        // Mid users can change status of their own tasks or tasks assigned to Juniors
        if ($assignedTo == $this->id) return true;
        $assignee = self::getById($assignedTo);
        return $assignee && $assignee->getLevel() === 'Junior';

      case 'Junior':
        // Juniors can only change status of their own tasks
        return $assignedTo == $this->id;

      default:
        return false;
    }
  }

  public function canCommentOnTask($taskId)
  {
    if ($this->level === 'Admin') {
      return true; // Admin can comment on any task
    }

    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT assigned_to, project_id FROM tasks WHERE id = ?');
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) return false;

    $assignedTo = $task['assigned_to'];
    $projectId = $task['project_id'];

    if ($this->level === 'Senior') {
      return self::isInProject($this->id, $projectId);  // Seniors on any task in project
    }

    if ($this->level === 'Mid') {
      return ($assignedTo == $this->id) || (self::getById($assignedTo)?->getLevel() === 'Junior');
    }

    if ($this->level === 'Junior') {
      return $assignedTo == $this->id;
    }

    return false;
  }

  public function canEditTask($taskId)
  {
    if ($this->level === 'Admin') {
      return true;
    }
    if ($this->level === 'Senior') {
      $task = Task::getById($taskId);
      if ($task) {
        return self::isInProject($this->id, $task->getProjectId());
      }
    }
    return false;
  }

  public function canEditComment($commentId)
  {
    if ($this->level === 'Admin') {
      return true;
    }
    $comment = Comment::getById($commentId);
    if ($comment) {
      return $comment->getUserId() == $this->id;
    }
    return false;
  }


  // Helpers
  public static function isProjectLead($userId, $projectId)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT team_lead_id FROM projects WHERE id = ?');
    $stmt->execute([$projectId]);
    return $stmt->fetchColumn() == $userId;
  }

  public static function isInProject($userId, $projectId)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT 1 FROM project_users WHERE project_id = ? AND user_id = ?');
    $stmt->execute([$projectId, $userId]);
    return (bool) $stmt->fetchColumn();
  }

  // Get assignable users for a project based on current user's level
  public function getAssignableUsers($projectId)
  {
    $team = self::getProjectTeam($projectId);
    $assignables = [];
    foreach ($team as $member) {
      if ($this->canAssignTaskTo($member['id'], $projectId)) {
        $assignables[] = $member;
      }
    }
    return $assignables;
  }

  // Get project team members
  public static function getProjectTeam($projectId)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT u.* FROM users u JOIN project_users pu ON u.id = pu.user_id WHERE pu.project_id = ?');
    $stmt->execute([$projectId]);
    return $stmt->fetchAll();
  }

  // Getters
  public function getId()
  {
    return $this->id;
  }
  public function getName()
  {
    return $this->name;
  }
  public function getEmail()
  {
    return $this->email;
  }
  public function getLevel()
  {
    return $this->level;
  }
  public function isTeamLead()
  {
    return $this->is_team_lead;
  }
  public function isApproved()
  {
    return $this->is_approved;
  }
  public function getPassword()
  {  // For internal verify only
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$this->id]);
    return $stmt->fetchColumn();
  }
}
