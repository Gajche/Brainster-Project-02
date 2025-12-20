<?php

require_once __DIR__ . '/../autoload.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

$userId    = $_SESSION['user_id'];
$userLevel = $_SESSION['user_level'] ?? '';

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
  case 'create':
    handleCreate($userId);
    break;
  case 'assign':
    handleAssign($userId);
    break;
  case 'unassign':
    handleUnassign($userId);
    break;
  case 'change_status':
    handleChangeStatus($userId);
    break;
  case 'update':
    handleUpdate($userId);
    break;
  case 'delete':
    handleDelete($userId);
    break;
  case 'add_comment':
    handleAddComment($userId);
    break;
  case 'edit_comment':
    handleEditComment($userId);
    break;
  case 'delete_comment':
    handleDeleteComment($userId);
    break;
  case 'get_task_details_html':
    handleGetTaskDetailsHtml($userId);
    break;
  case 'get_task_edit_form':
    handleGetTaskEditForm($userId);
    break;
  case 'get_comment_edit_form':
    handleGetCommentEditForm($userId);
    break;
  default:
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
    exit;
}


// ============================================================================
// AJAX HTML RETURNERS
// ============================================================================

function handleGetTaskEditForm($userId)
{
  $taskId = $_GET['task_id'] ?? 0;
  $task   = Task::getById($taskId);
  $user   = User::getById($userId);

  if (!$task || !$user->canEditTask($task->getId())) {
    apiError('forbidden', 'Access denied.', 403);
  }

  $formHtml = '
    <form class="ajax-form" data-reload="task-modal" data-task-id="' . $task->getId() . '" method="POST" action="' . Config::getBaseUrl() . 'controllers/task_controller.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="task_id" value="' . $task->getId() . '">
      <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" value="' . htmlspecialchars($task->getTitle()) . '" required>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3">' . htmlspecialchars($task->getDescription()) . '</textarea>
      </div>
      <button type="submit" class="btn btn-primary">Update Task</button>
    </form>';

  apiSuccess(['body' => $formHtml], 'Edit form loaded');
}

function handleGetCommentEditForm($userId)
{
  $commentId = $_GET['comment_id'] ?? 0;
  $comment   = Comment::getById($commentId);
  $user      = User::getById($userId);

  if (!$comment || !$user->canEditComment($comment->getId())) {
    apiError('forbidden', 'Access denied.', 403);
  }

  $formHtml = '
    <form class="ajax-form" data-reload="task-modal" data-task-id="' . $comment->getTaskId() . '" method="POST" action="' . Config::getBaseUrl() . 'controllers/task_controller.php">
      <input type="hidden" name="action" value="edit_comment">
      <input type="hidden" name="comment_id" value="' . $comment->getId() . '">
      <div class="mb-3">
        <label for="content" class="form-label">Comment</label>
        <textarea class="form-control" id="content" name="content" rows="3">' . htmlspecialchars($comment->getContent()) . '</textarea>
      </div>
      <button type="submit" class="btn btn-primary">Update Comment</button>
    </form>';

  apiSuccess(['body' => $formHtml], 'Edit comment form loaded');
}

function handleGetTaskDetailsHtml($userId)
{
  $taskId = $_GET['task_id'] ?? 0;
  $task   = Task::getById($taskId);
  $user   = User::getById($userId);

  if (!$task || (!isAdmin() && !$user->isInProject($userId, $task->getProjectId()))) {
    apiError('not_found', 'Task not found or access denied.', 404);
    return;
  }

  $userLevel       = $_SESSION['user_level'] ?? null;
  $isTeamLead      = Project::getById($task->getProjectId())->canManageTeam($userId);
  $canAssign       = isAdmin() || $isTeamLead || !empty($user->getAssignableUsers($task->getProjectId())); // Declared but Not used - REDUNDANT REDUNDANT
  $canChangeStatus = $user->canChangeTaskStatus($task->getId()); // Declared but Not used - REDUNDANT
  $canEditDeleteTask = isAdmin() || $isTeamLead || ($userLevel === 'Senior' && $task->getAssignedTo() == $userId); // Declared but Not used - REDUNDANT

  ob_start();
  require_once Config::ROOT_DIR . '/views/partials/task_modal_content.php';
  $modalBodyHtml = ob_get_clean();

  apiSuccess([
    'title' => 'Task: ' . htmlspecialchars($task->getTitle()),
    'body'  => $modalBodyHtml
  ], 'Task details loaded');
}


// ============================================================================
// TASK CRUD ACTIONS
// ============================================================================

function handleCreate($userId)
{
  $projectId   = $_POST['project_id'] ?? 0;
  $title       = trim($_POST['title'] ?? '');
  $description = $_POST['description'] ?? '';
  $assigneeId  = !empty($_POST['assignee_id']) ? $_POST['assignee_id'] : null;

  // Validation
  if (empty($projectId) || empty($title)) {
    handleResponse(false, 'Title and project are required.', null, 400);
    return;
  }

  // Create task
  $data = [
    'project_id'  => $projectId,
    'title'       => $title,
    'description' => $description
  ];

  $newTaskId = Task::create($data, $userId);

  if ($newTaskId) {
    // Assign if needed
    if ($assigneeId) {
      $task = Task::getById($newTaskId);
      if ($task) {
        $task->assign($assigneeId, $userId);
      }
    }
    handleResponse(true, 'Task created successfully.');
  } else {
    handleResponse(false, 'You are not allowed to create tasks in this project.', null, 403);
  }
}

function handleAssign($userId)
{
  $taskId     = $_POST['task_id'] ?? 0;
  $assigneeId = $_POST['assignee_id'] ?? 0;

  $task = Task::getById($taskId);

  if (!$task) {
    handleResponse(false, 'Task not found.', null, 404);
    return;
  }

  if ($task->assign($assigneeId, $userId)) {
    handleResponse(true, 'Task assigned successfully.');
  } else {
    handleResponse(false, 'You are not allowed to assign this task to that user.', null, 403);
  }
}

function handleUnassign($userId)
{
  $taskId = $_POST['task_id'] ?? 0;

  $task = Task::getById($taskId);

  if (!$task) {
    handleResponse(false, 'Task not found.', null, 404);
    return;
  }

  if ($task->assign(null, $userId)) {
    handleResponse(true, 'Task unassigned successfully.');
  } else {
    handleResponse(false, 'You do not have permission to unassign this task.', null, 403);
  }
}

function handleChangeStatus($userId)
{
  $taskId    = $_POST['task_id'] ?? 0;
  $newStatus = $_POST['status'] ?? '';

  $task = Task::getById($taskId);

  if (!$task) {
    handleResponse(false, 'Task not found.', null, 404);
    return;
  }

  if ($task->updateStatus($newStatus, $userId)) {
    handleResponse(true, 'Status updated.');
  } else {
    handleResponse(false, 'You cannot change the status of this task.', null, 403);
  }
}

function handleUpdate($userId)
{
  $taskId      = $_POST['task_id'] ?? 0;
  $title       = trim($_POST['title'] ?? '');
  $description = $_POST['description'] ?? '';

  $task = Task::getById($taskId);

  if (!$task) {
    handleResponse(false, 'Task not found.', null, 404);
    return;
  }

  $data = [];
  if (!empty($title))               $data['title']       = $title;
  if (isset($_POST['description'])) $data['description'] = $description;

  if ($task->update($data, $userId)) {
    handleResponse(true, 'Task updated.');
  } else {
    handleResponse(false, 'You do not have permission to edit this task.', null, 403);
  }
}

function handleDelete($userId)
{
  $taskId = $_POST['task_id'] ?? 0;

  if (Task::delete($taskId, $userId)) {
    handleResponse(true, 'Task deleted successfully.');
  } else {
    handleResponse(false, 'You are not allowed to delete this task.', null, 403);
  }
}


// ============================================================================
// COMMENT ACTIONS
// ============================================================================

function handleAddComment($userId)
{
  $taskId  = $_POST['task_id'] ?? 0;
  $content = trim($_POST['content'] ?? '');

  // Validation
  if (empty($taskId) || empty($content)) {
    handleResponse(false, 'Comment cannot be empty.', null, 400);
    return;
  }

  $commentData = [
    'task_id' => $taskId,
    'user_id' => $userId,
    'content' => $content
  ];

  $newCommentId = Comment::create($commentData);

  if ($newCommentId) {
    $comment = Comment::getById($newCommentId)->getDetails();
    handleResponse(true, 'Comment added.', ['comment' => $comment]);
  } else {
    handleResponse(false, 'You are not allowed to comment on this task.', null, 403);
  }
}

function handleEditComment($userId)
{
  $commentId = $_POST['comment_id'] ?? 0;
  $content   = trim($_POST['content'] ?? '');

  $comment = Comment::getById($commentId);

  if (!$comment) {
    handleResponse(false, 'Comment not found.', null, 404);
    return;
  }

  if ($comment->update($content, $userId)) {
    handleResponse(true, 'Comment updated.');
  } else {
    handleResponse(false, 'You can only edit your own comments (or Admin).', null, 403);
  }
}

function handleDeleteComment($userId)
{
  $commentId = $_POST['comment_id'] ?? 0;

  if (Comment::delete($commentId, $userId)) {
    handleResponse(true, 'Comment deleted successfully.');
  } else {
    handleResponse(false, 'You can only delete your own comments (or Admin).', null, 403);
  }
}
