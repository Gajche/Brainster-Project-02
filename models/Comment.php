<?php

require_once __DIR__ . '/../includes/config.php';  // Relative path to load Config class first
// require_once Config::ROOT_DIR . '/includes/config.php';
require_once Config::ROOT_DIR . '/models/Database.php';
require_once Config::ROOT_DIR . '/models/User.php';
require_once Config::ROOT_DIR . '/models/Task.php';

class Comment
{
  private $id;
  private $task_id;
  private $user_id;
  private $content;
  private $created_at;
  private $edited;

  public function __construct($data)
  {
    $this->id = $data['id'] ?? null;
    $this->task_id = $data['task_id'] ?? null;
    $this->user_id = $data['user_id'] ?? null;
    $this->content = $data['content'] ?? '';
    $this->created_at = $data['created_at'] ?? null;
    $this->edited = $data['edited'] ?? 0;
  }

  // Get comment by ID
  public static function getById($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT * FROM comments WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    return $data ? new self($data) : null;
  }

  // Get comments for a task
  public static function getByTask($taskId)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT c.*, u.name AS user_name FROM comments c JOIN users u ON c.user_id = u.id WHERE task_id = ? ORDER BY created_at ASC');
    $stmt->execute([$taskId]);
    return $stmt->fetchAll();
  }

  // Create comment (with permission check)
  public static function create($data)
  {
    $user = User::getById($data['user_id']);
    if (!$user || !$user->canCommentOnTask($data['task_id'])) {
      return false;
    }
    $db = Database::getInstance();
    $stmt = $db->prepare('INSERT INTO comments (task_id, user_id, content) VALUES (?, ?, ?)');
    if ($stmt->execute([$data['task_id'], $data['user_id'], $data['content']])) {
      return $db->lastInsertId();
    }
    return false;
  }

  // Update comment (owner only, mark as edited)
  public function update($content, $userId)
  {
    if ($this->user_id != $userId && User::getById($userId)->getLevel() !== 'Admin') {
      return false;
    }
    $db = Database::getInstance();
    $stmt = $db->prepare('UPDATE comments SET content = ?, edited = 1 WHERE id = ?');
    return $stmt->execute([$content, $this->id]);
  }

  // Delete comment (owner or Admin)
  public static function delete($id, $userId)
  {
    $comment = self::getById($id);
    if (!$comment) return false;
    $user = User::getById($userId);
    if (!$user || ($comment->user_id != $userId && $user->getLevel() !== 'Admin')) {
      return false;
    }
    $db = Database::getInstance();
    $stmt = $db->prepare('DELETE FROM comments WHERE id = ?');
    return $stmt->execute([$id]);
  }

  // Get comment details for AJAX response
  public function getDetails()
  {
      $user = User::getById($this->user_id);
      return [
          'id' => $this->id,
          'task_id' => $this->task_id,
          'user_id' => $this->user_id,
          'user_name' => $user ? $user->getName() : 'Unknown',
          'content' => $this->content,
          'created_at' => $this->created_at,
          'edited' => $this->edited
      ];
  }

  // Getters
  public function getId()
  {
    return $this->id;
  }
  public function getTaskId()
  {
    return $this->task_id;
  }
  public function getUserId()
  {
    return $this->user_id;
  }
  public function getContent()
  {
    return $this->content;
  }
  public function getCreatedAt()
  {
    return $this->created_at;
  }
  public function isEdited()
  {
    return $this->edited;
  }
}
