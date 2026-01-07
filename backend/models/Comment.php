<?php

require_once __DIR__ . '/../../autoload.php';

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
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT * FROM comments WHERE id = ?');
      $stmt->execute([$id]);
      $data = $stmt->fetch();
      return $data ? new self($data) : null;
    } catch (PDOException $e) {
      error_log('[COMMENT getById] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Get comments for a task
  public static function getByTask($taskId)
  {
    try {
      $db = Database::getInstance();
      $stmt = $db->prepare('SELECT c.*, u.name AS user_name FROM comments c JOIN users u ON c.user_id = u.id WHERE task_id = ? ORDER BY created_at ASC');
      $stmt->execute([$taskId]);
      return $stmt->fetchAll();
    } catch (PDOException $e) {
      error_log('[COMMENT getByTask] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Create comment (with permission check)
  public static function create($data)
  {
    try {
      // User::getById may throw DatabaseException - that's OK.
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
    } catch (PDOException $e) {
      error_log('[COMMENT create] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Update comment (owner only, mark as edited)
  public function update($content, $userId)
  {
    try {
      // User::getById may throw DatabaseException - that's OK.
      if ($this->user_id != $userId && User::getById($userId)->getLevel() !== 'Admin') {
        return false;
      }

      $db = Database::getInstance();
      $stmt = $db->prepare('UPDATE comments SET content = ?, edited = 1 WHERE id = ?');
      return $stmt->execute([$content, $this->id]);
    } catch (PDOException $e) {
      error_log('[COMMENT update] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Delete comment (owner or Admin)
  public static function delete($id, $userId)
  {
    try {
      // This may throw DatabaseException if comments table is missing
      $comment = self::getById($id);
      if (!$comment) return false;

      // User::getById may throw DatabaseException if users table is missing
      $user = User::getById($userId);
      if (!$user || ($comment->user_id != $userId && $user->getLevel() !== 'Admin')) {
        return false;
      }

      $db = Database::getInstance();
      $stmt = $db->prepare('DELETE FROM comments WHERE id = ?');
      return $stmt->execute([$id]);
    } catch (PDOException $e) {
      error_log('[COMMENT delete] ' . $e->getMessage());
      throw new DatabaseException();
    }
  }

  // Get comment details for AJAX response
  public function getDetails()
  {
    // Note: This method doesn't directly query database, but User::getById might
    // We should catch DatabaseException here to provide fallback
    try {
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
    } catch (DatabaseException $e) {
      // If we can't get user info, return basic comment info without user details
      error_log('[COMMENT getDetails] Failed to get user info: ' . $e->getMessage());
      return [
        'id' => $this->id,
        'task_id' => $this->task_id,
        'user_id' => $this->user_id,
        'user_name' => 'Unknown',
        'content' => $this->content,
        'created_at' => $this->created_at,
        'edited' => $this->edited,
        'error' => 'Unable to load user information'
      ];
    }
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
