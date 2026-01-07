<?php

require_once __DIR__ . '/../../autoload.php';

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
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
      $stmt->execute([$id]);
      $data = $stmt->fetch();
      return $data ? new self($data) : null;
    } catch (PDOException $e) {
      error_log('[USER getById] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Get user by email or name for login
  public static function getByLogin($login)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT * FROM users WHERE email = ? OR name = ?');
      $stmt->execute([$login, $login]);
      $data = $stmt->fetch();
      return $data ? new self($data) : null;
    } catch (PDOException $e) {
      error_log('[USER getByLogin] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Register new user (self-reg, pending approval)
  public static function register($name, $email, $password, $level)
  {
    try {
      $db = Database::getInstance();
      $hashed = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $db->prepare('INSERT INTO users (name, email, password, level) VALUES (?, ?, ?, ?)');
      return $stmt->execute([$name, $email, $hashed, $level]);
    } catch (PDOException $e) {
      error_log('[USER register] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Admin create user (auto-approved, random pw)
  public static function adminCreate($name, $email, $password = null, $level, $is_team_lead = 0, $adminId = null)
  {
    try {
      // HARD SAFETY: only Seniors can be Team Leads
      if ($level !== 'Senior') {
        $is_team_lead = 0;
      }

      $db = Database::getInstance();
      $randomPw = $password ?? bin2hex(random_bytes(8));
      $hashed = password_hash($randomPw, PASSWORD_DEFAULT);
      $stmt = $db->prepare('INSERT INTO users (name, email, password, level, is_team_lead, is_approved) VALUES (?, ?, ?, ?, ?, 1)');
      $exec = $stmt->execute([$name, $email, $hashed, $level, $is_team_lead]);

      if ($exec) {
        $newUserId = $db->lastInsertId();

        // LOG THE PASSWORD TO FILE
        self::logPassword('USER_CREATED', $newUserId, $email, $randomPw, $adminId);

        return ['id' => $newUserId, 'random_pw' => $randomPw];
      }
      return false;
    } catch (PDOException $e) {
      error_log('[USER adminCreate] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }


  // Admin reset password
  public static function adminResetPassword($userId, $adminId = null)
  {
    try {
      $db = Database::getInstance();
      $randomPw = bin2hex(random_bytes(8));
      $hashed = password_hash($randomPw, PASSWORD_DEFAULT);
      $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');

      if ($stmt->execute([$hashed, $userId])) {
        // Get user email for logging
        $userStmt = $db->prepare('SELECT email FROM users WHERE id = ?');
        $userStmt->execute([$userId]);
        $userEmail = $userStmt->fetchColumn();

        // LOG THE PASSWORD TO FILE
        self::logPassword('PASSWORD_RESET', $userId, $userEmail, $randomPw, $adminId);

        return $randomPw;
      }
      return false;
    } catch (PDOException $e) {
      error_log('[USER adminResetPassword] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }


  // Approve user (Admin only)
  public static function approve($id)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('UPDATE users SET is_approved = 1 WHERE id = ?');
      return $stmt->execute([$id]);
    } catch (PDOException $e) {
      error_log('[USER approve] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Change password 
  public function changePassword($oldPw, $newPw)
  {
    try {
      $db = Database::getInstance();
      // getPassword() might throw DatabaseException 
      $user = self::getById($this->id);
      if (password_verify($oldPw, $user->getPassword())) {
        $hashed = password_hash($newPw, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([$hashed, $this->id]);
      }
      return false;
    } catch (PDOException $e) {
      error_log('[USER changePassword] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Log password
  private static function logPassword($action, $userId, $email, $password, $adminId = null)
  {
    $logFile = Config::ROOT_DIR . '/password_log/user_passwords.txt'; // Or any path you prefer
    $timestamp = date('Y-m-d H:i:s');
    $adminInfo = $adminId ? " by Admin ID: $adminId" : '';

    $logEntry = "[$timestamp] $action - User ID: $userId, Email: $email, Password: $password$adminInfo\n";

    // Create log file if doesn't exist, append if exists
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    // Optional: Set permissions (readable only by server)
    chmod($logFile, 0600);
  }


  // Get all users (for Admin or dropdowns)
  public static function getAll()
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->query('SELECT * FROM users');
      return $stmt->fetchAll();
    } catch (PDOException $e) {
      error_log('[USER getAll] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Update user (Admin)
  public static function update($id, $data)
  {
    try {
      $db = Database::getInstance();

      // HARD SAFETY: only Seniors can be Team Leads
      if (isset($data['level']) && $data['level'] !== 'Senior') {
        $data['is_team_lead'] = 0;
      }

      // Extra safety: level not sent but checkbox is
      if (!isset($data['level']) && isset($data['is_team_lead'])) {
        // getById() might throw DatabaseException 
        $existing = self::getById($id);
        if ($existing && $existing->getLevel() !== 'Senior') {
          $data['is_team_lead'] = 0;
        }
      }

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
    } catch (PDOException $e) {
      error_log('[USER update] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Delete user (Admin)
  public static function delete($id)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
      return $stmt->execute([$id]);
    } catch (PDOException $e) {
      error_log('[USER delete] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Check if user can create task in a project
  public function canCreateTask($projectId)
  {
    try {
      if ($this->level === 'Admin') {
        return true; // Admin can create tasks in any project
      }
      // Team Leads and Seniors in the project can create tasks.
      if ($this->level === 'Senior') {
        // isInProject() might throw DatabaseException 
        return self::isInProject($this->id, $projectId);
      }
      return false;
    } catch (DatabaseException $e) {
      // If we can't verify permission, return false for safety
      error_log('[USER canCreateTask] Failed to verify permission: ' . $e->getMessage());
      return false;
    }
  }

  // Check if user can assign task to another user
  public function canAssignTaskTo($assigneeId, $projectId = null)
  {
    try {
      // If assigneeId is null, it means we are unassigning the task.
      // Mid users CANNOT unassign tasks according to specification
      if ($assigneeId === null) {
        // Only Admin, Team Leads, and regular Seniors can unassign
        // Mid users explicitly cannot unassign
        return $this->level !== 'Mid'; // Mid returns false, others true
      }

      // getById() might throw DatabaseException 
      $assignee = self::getById($assigneeId);
      if (!$assignee) return false;

      $assigneeLevel = $assignee->getLevel();

      switch ($this->level) {
        case 'Admin':
          return true; // Admin can assign to anyone 

        case 'Senior':
          // isProjectLead() might throw DatabaseException 
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
    } catch (DatabaseException $e) {
      // If we can't verify permission, return false for safety
      error_log('[USER canAssignTaskTo] Failed to verify permission: ' . $e->getMessage());
      return false;
    }
  }

  // Check if user can change task status
  public function canChangeTaskStatus($taskId)
  {
    try {
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
          // isInProject() might throw DatabaseException 
          return self::isInProject($this->id, $projectId);

        case 'Mid':
          // Mid users can change status of their own tasks or tasks assigned to Juniors
          if ($assignedTo == $this->id) return true;
          // getById() might throw DatabaseException 
          $assignee = self::getById($assignedTo);
          return $assignee && $assignee->getLevel() === 'Junior';

        case 'Junior':
          // Juniors can only change status of their own tasks
          return $assignedTo == $this->id;

        default:
          return false;
      }
    } catch (PDOException $e) {
      error_log('[USER canChangeTaskStatus] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Check if user can comment on a task
  public function canCommentOnTask($taskId)
  {
    try {
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
        // isInProject() might throw DatabaseException 
        return self::isInProject($this->id, $projectId);  // Seniors on any task in project
      }

      if ($this->level === 'Mid') {
        return ($assignedTo == $this->id) || (self::getById($assignedTo)?->getLevel() === 'Junior');
      }

      if ($this->level === 'Junior') {
        return $assignedTo == $this->id;
      }

      return false;
    } catch (PDOException $e) {
      error_log('[USER canCommentOnTask] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Check if user can edit a task
  public function canEditTask($taskId)
  {
    try {
      if ($this->level === 'Admin') {
        return true;
      }
      if ($this->level === 'Senior') {
        // Task::getById() might throw DatabaseException 
        $task = Task::getById($taskId);
        if ($task) {
          // isInProject() might throw DatabaseException 
          return self::isInProject($this->id, $task->getProjectId());
        }
      }
      return false;
    } catch (DatabaseException $e) {
      // If we can't verify permission, return false for safety
      error_log('[USER canEditTask] Failed to verify permission: ' . $e->getMessage());
      return false;
    }
  }

  // Check if user can edit a comment
  public function canEditComment($commentId)
  {
    try {
      if ($this->level === 'Admin') {
        return true;
      }
      // Comment::getById() might throw DatabaseException 
      $comment = Comment::getById($commentId);
      if ($comment) {
        return $comment->getUserId() == $this->id;
      }
      return false;
    } catch (DatabaseException $e) {
      // If we can't verify permission, return false for safety
      error_log('[USER canEditComment] Failed to verify permission: ' . $e->getMessage());
      return false;
    }
  }


  // Helpers
  // Check if user is project lead
  public static function isProjectLead($userId, $projectId)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT team_lead_id FROM projects WHERE id = ?');
      $stmt->execute([$projectId]);
      return $stmt->fetchColumn() == $userId;
    } catch (PDOException $e) {
      error_log('[USER isProjectLead] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Check if user is in project team
  public static function isInProject($userId, $projectId)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT 1 FROM project_users WHERE project_id = ? AND user_id = ?');
      $stmt->execute([$projectId, $userId]);
      return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
      error_log('[USER isInProject] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Get assignable users for a project based on current user's level
  public function getAssignableUsers($projectId)
  {
    try {
      // getProjectTeam() might throw DatabaseException 
      $team = self::getProjectTeam($projectId);
      $assignables = [];
      foreach ($team as $member) {
        if ($this->canAssignTaskTo($member['id'], $projectId)) {
          $assignables[] = $member;
        }
      }
      return $assignables;
    } catch (DatabaseException $e) {
      // If we can't get assignable users, return empty array for safety
      error_log('[USER getAssignableUsers] Failed to get assignable users: ' . $e->getMessage());
      return [];
    }
  }

  // Get project team members
  public static function getProjectTeam($projectId)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT u.* FROM users u JOIN project_users pu ON u.id = pu.user_id WHERE pu.project_id = ?');
      $stmt->execute([$projectId]);
      return $stmt->fetchAll();
    } catch (PDOException $e) {
      error_log('[USER getProjectTeam] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Getters
  public function getId(): ?int
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

  public function getLevel(): string
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
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
      $stmt->execute([$this->id]);
      return $stmt->fetchColumn();
    } catch (PDOException $e) {
      error_log('[USER getPassword] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }
}
