<?php

require_once __DIR__ . '/../../autoload.php'; // Ensures all necessary classes and functions are loaded.

// Must be logged in
// Enforces authentication: Redirects unauthenticated users to the login page.
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

$userId    = $_SESSION['user_id']; // Retrieves the ID of the currently authenticated user.
$userLevel = $_SESSION['user_level'] ?? ''; // Retrieves the user's privilege level, defaulting to an empty string if not set.

// Handle actions
// Determines the requested action from POST, then GET parameters, defaulting to an empty string.
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Dispatches the request to the appropriate handler function based on the 'action' parameter.
switch ($action) {
  case 'create':
    // Handles the creation of a new task.
    // @param int $userId The ID of the user performing the action.
    handleCreate($userId);
    break;
  case 'assign':
    // Handles assigning a task to a user.
    // @param int $userId The ID of the user performing the action.
    handleAssign($userId);
    break;
  case 'unassign':
    // Handles unassigning a task from a user.
    // @param int $userId The ID of the user performing the action.
    handleUnassign($userId);
    break;
  case 'change_status':
    // Handles changing the status of a task.
    // @param int $userId The ID of the user performing the action.
    handleChangeStatus($userId);
    break;
  case 'update':
    // Handles updating an existing task's details.
    // @param int $userId The ID of the user performing the action.
    handleUpdate($userId);
    break;
  case 'delete':
    // Handles deleting a task.
    // @param int $userId The ID of the user performing the action.
    handleDelete($userId);
    break;
  case 'add_comment':
    // Handles adding a new comment to a task.
    // @param int $userId The ID of the user performing the action.
    handleAddComment($userId);
    break;
  case 'edit_comment':
    // Handles editing an existing comment.
    // @param int $userId The ID of the user performing the action.
    handleEditComment($userId);
    break;
  case 'delete_comment':
    // Handles deleting a comment.
    // @param int $userId The ID of the user performing the action.
    handleDeleteComment($userId);
    break;
  case 'get_task_details_html':
    // Handles fetching the HTML content for task details, typically for a modal.
    // @param int $userId The ID of the user requesting the details.
    handleGetTaskDetailsHtml($userId);
    break;
  case 'get_task_edit_form':
    // Handles fetching the HTML form for editing a task.
    // @param int $userId The ID of the user requesting the form.
    handleGetTaskEditForm($userId);
    break;
  case 'get_comment_edit_form':
    // Handles fetching the HTML form for editing a comment.
    // @param int $userId The ID of the user requesting the form.
    handleGetCommentEditForm($userId);
    break;
  default:
    // If no valid action is provided, redirects to the dashboard.
    header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
    exit;
}



// AJAX HTML RETURNERS

/**
 * Handles generating and returning the HTML form for editing a specific task.
 * This is typically used for AJAX requests to populate a modal or section.
 *
 * @param int $userId The ID of the user requesting the form. Used for permission checks.
 * @return void Returns an API success response with the form HTML or an error response.
 */
function handleGetTaskEditForm($userId)
{
  $taskId = $_GET['task_id'] ?? 0; // Retrieves the task ID from GET parameters.
  $task   = Task::getById($taskId); // Fetches the task object from the database.
  $user   = User::getById($userId); // Fetches the user object for permission checks.

  // Verifies if the task exists and if the current user has permission to edit it.
  if (!$task || !$user->canEditTask($task->getId())) {
    apiError('forbidden', 'Access denied.', 403);
    return; // Important to return after apiError to prevent further execution.
  }

  // Constructs the HTML form for task editing.
  // Note: Form actions point back to this controller for processing updates.
  $formHtml = '
    <form class="ajax-form" data-reload="task-modal" data-task-id="' . $task->getId() . '" method="POST" action="' . Config::getBaseUrl() . 'backend/controllers/task_controller.php">
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
      <button type="submit" class="btn btn-primary">
  <i class="fas fa-save me-1"></i> Update Task
</button>
    </form>';

  // Sends a successful API response with the generated form HTML.
  apiSuccess(['body' => $formHtml], 'Edit form loaded');
}

/**
 * Handles generating and returning the HTML form for editing a specific comment.
 * This is typically used for AJAX requests to populate a modal or section.
 *
 * @param int $userId The ID of the user requesting the form. Used for permission checks.
 * @return void Returns an API success response with the form HTML or an error response.
 */
function handleGetCommentEditForm($userId)
{
  $commentId = $_GET['comment_id'] ?? 0; // Retrieves the comment ID from GET parameters.
  $comment   = Comment::getById($commentId); // Fetches the comment object from the database.
  $user      = User::getById($userId); // Fetches the user object for permission checks.

  // Verifies if the comment exists and if the current user has permission to edit it.
  if (!$comment || !$user->canEditComment($comment->getId())) {
    apiError('forbidden', 'Access denied.', 403);
    return; // Important to return after apiError to prevent further execution.
  }

  // Constructs the HTML form for comment editing.
  // Note: Form actions point back to this controller for processing updates.
  $formHtml = '
    <form class="ajax-form" data-reload="task-modal" data-task-id="' . $comment->getTaskId() . '" method="POST" action="' . Config::getBaseUrl() . 'backend/controllers/task_controller.php">
      <input type="hidden" name="action" value="edit_comment">
      <input type="hidden" name="comment_id" value="' . $comment->getId() . '">
      <div class="mb-3">
        <label for="content" class="form-label">Comment</label>
        <textarea class="form-control" id="content" name="content" rows="3">' . htmlspecialchars($comment->getContent()) . '</textarea>
      </div>
      <button type="submit" class="btn btn-primary">
  <i class="fas fa-save me-1"></i> Update Comment
</button>
    </form>';

  // Sends a successful API response with the generated form HTML.
  apiSuccess(['body' => $formHtml], 'Edit comment form loaded');
}

/**
 * Handles generating and returning the HTML content for displaying task details.
 * This is typically used for AJAX requests to populate a task details modal.
 *
 * @param int $userId The ID of the user requesting the task details. Used for permission checks.
 * @return void Returns an API success response with the task details HTML or an error response.
 */
function handleGetTaskDetailsHtml($userId)
{
  $taskId = $_GET['task_id'] ?? 0; // Retrieves the task ID from GET parameters.
  $task   = Task::getById($taskId); // Fetches the task object from the database.
  $user   = User::getById($userId); // Fetches the user object for permission checks.

  // Authorizes access: Checks if the task exists and if the user is an admin or part of the project.
  if (!$task || (!isAdmin() && !$user->isInProject($userId, $task->getProjectId()))) {
    apiError('not_found', 'Task not found or access denied.', 404);
    return;
  }

  $userLevel       = $_SESSION['user_level'] ?? null;
  // Determines if the current user is a team lead for the task's project.
  $isTeamLead      = Project::getById($task->getProjectId())->canManageTeam($userId);
  // Checks if the user has permissions to assign tasks. Declared but Not used - REDUNDANT
  $canAssign       = isAdmin() || $isTeamLead || !empty($user->getAssignableUsers($task->getProjectId()));
  // Checks if the user has permissions to change task status. Declared but Not used - REDUNDANT
  $canChangeStatus = $user->canChangeTaskStatus($task->getId());
  // Checks if the user has permissions to edit or delete the task. Declared but Not used - REDUNDANT
  $canEditDeleteTask = isAdmin() || $isTeamLead || ($userLevel === 'Senior' && $task->getAssignedTo() == $userId);

  // Buffers the output of the task modal content partial view.
  ob_start();
  require_once Config::ROOT_DIR . '/backend/views/partials/task_modal_content.php';
  $modalBodyHtml = ob_get_clean(); // Captures the buffered HTML content.

  // Sends a successful API response with the task title and the generated modal body HTML.
  apiSuccess([
    'title' => 'Task: ' . htmlspecialchars($task->getTitle()),
    'body'  => $modalBodyHtml
  ], 'Task details loaded');
}



// TASK CRUD ACTIONS

/**
 * Handles the creation of a new task based on submitted form data.
 * Performs validation and calls the Task model to create the record.
 *
 * @param int $userId The ID of the user attempting to create the task.
 * @return void Returns an API response indicating success or failure.
 */
function handleCreate($userId)
{
  $projectId   = $_POST['project_id'] ?? 0;   // The project ID to which the task belongs.
  $title       = trim($_POST['title'] ?? ''); // The title of the new task.
  $description = $_POST['description'] ?? ''; // The description of the new task.
  $assigneeId  = !empty($_POST['assignee_id']) ? $_POST['assignee_id'] : null; // Optional assignee ID.

  // Validation
  // Ensures essential fields are provided before attempting task creation.
  if (empty($projectId) || empty($title)) {
    handleResponse(false, 'Title and project are required.', null, 400);
    return;
  }

  // Prepares data array for task creation.
  $data = [
    'project_id'  => $projectId,
    'title'       => $title,
    'description' => $description
  ];

  // Attempts to create the task. The $userId is used for permission checks within Task::create.
  $newTaskId = Task::create($data, $userId);

  if ($newTaskId) {
    // Assign if needed
    // If an assignee was specified and the task was created successfully, attempt to assign it.
    if ($assigneeId) {
      $task = Task::getById($newTaskId);
      if ($task) {
        $task->assign($assigneeId, $userId); // Assigns the task using the task object.
      }
    }
    handleResponse(true, 'Task created successfully.');
  } else {
    // If task creation fails, it's typically due to permission issues.
    handleResponse(false, 'You are not allowed to create tasks in this project.', null, 403);
  }
}

/**
 * Handles assigning a task to a specified user.
 * Performs validation and calls the Task model to update the assignment.
 *
 * @param int $userId The ID of the user attempting to assign the task.
 * @return void Returns an API response indicating success or failure.
 */
function handleAssign($userId)
{
  $taskId     = $_POST['task_id'] ?? 0;     // The ID of the task to be assigned.
  $assigneeId = $_POST['assignee_id'] ?? 0; // The ID of the user to assign the task to.

  $task = Task::getById($taskId); // Fetches the task object.

  // Validates if the task exists.
  if (!$task) {
    handleResponse(false, 'Task not found.', null, 404);
    return;
  }

  // Attempts to assign the task. $userId is used for permission checks.
  if ($task->assign($assigneeId, $userId)) {
    handleResponse(true, 'Task assigned successfully.');
  } else {
    // If assignment fails, it's typically due to permission issues or invalid assignee.
    handleResponse(false, 'You are not allowed to assign this task to that user.', null, 403);
  }
}

/**
 * Handles unassigning a task from its current assignee.
 * Performs validation and calls the Task model to update the assignment.
 *
 * @param int $userId The ID of the user attempting to unassign the task.
 * @return void Returns an API response indicating success or failure.
 */
function handleUnassign($userId)
{
  $taskId = $_POST['task_id'] ?? 0; // The ID of the task to be unassigned.

  $task = Task::getById($taskId); // Fetches the task object.

  // Validates if the task exists.
  if (!$task) {
    handleResponse(false, 'Task not found.', null, 404);
    return;
  }

  // Attempts to unassign the task by setting assignee to null. $userId is used for permission checks.
  if ($task->assign(null, $userId)) {
    handleResponse(true, 'Task unassigned successfully.');
  } else {
    // If unassignment fails, it's typically due to permission issues.
    handleResponse(false, 'You do not have permission to unassign this task.', null, 403);
  }
}

/**
 * Handles changing the status of a task.
 * Performs validation and calls the Task model to update the status.
 *
 * @param int $userId The ID of the user attempting to change the status.
 * @return void Returns an API response indicating success or failure.
 */
function handleChangeStatus($userId)
{
  $taskId    = $_POST['task_id'] ?? 0; // The ID of the task whose status is to be changed.
  $newStatus = $_POST['status'] ?? ''; // The new status to set for the task.

  $task = Task::getById($taskId); // Fetches the task object.

  // Validates if the task exists.
  if (!$task) {
    handleResponse(false, 'Task not found.', null, 404);
    return;
  }

  // Attempts to update the task's status. $userId is used for permission checks.
  if ($task->updateStatus($newStatus, $userId)) {
    handleResponse(true, 'Status updated.');
  } else {
    // If status update fails, it's typically due to permission issues or invalid status transition.
    handleResponse(false, 'You cannot change the status of this task.', null, 403);
  }
}

/**
 * Handles updating the title and/or description of a task.
 * Performs validation and calls the Task model to update the task details.
 *
 * @param int $userId The ID of the user attempting to update the task.
 * @return void Returns an API response indicating success or failure.
 */
function handleUpdate($userId)
{
  $taskId      = $_POST['task_id'] ?? 0;     // The ID of the task to be updated.
  $title       = trim($_POST['title'] ?? ''); // The new title for the task (if provided).
  $description = $_POST['description'] ?? ''; // The new description for the task (if provided).

  $task = Task::getById($taskId); // Fetches the task object.

  // Validates if the task exists.
  if (!$task) {
    handleResponse(false, 'Task not found.', null, 404);
    return;
  }

  $data = [];
  // Populates the data array with provided fields for update.
  // Only adds title if it's not empty.
  if (!empty($title))               $data['title']       = $title;
  // Adds description if the parameter was present, even if empty (to allow clearing description).
  if (isset($_POST['description'])) $data['description'] = $description;

  // Attempts to update the task with the prepared data. $userId is used for permission checks.
  if ($task->update($data, $userId)) {
    handleResponse(true, 'Task updated.');
  } else {
    // If update fails, it's typically due to permission issues.
    handleResponse(false, 'You do not have permission to edit this task.', null, 403);
  }
}

/**
 * Handles deleting a task.
 * Calls the Task model to perform the deletion.
 *
 * @param int $userId The ID of the user attempting to delete the task.
 * @return void Returns an API response indicating success or failure.
 */
function handleDelete($userId)
{
  $taskId = $_POST['task_id'] ?? 0; // The ID of the task to be deleted.

  // Attempts to delete the task. $userId is used for permission checks within Task::delete.
  if (Task::delete($taskId, $userId)) {
    handleResponse(true, 'Task deleted successfully.');
  } else {
    // If deletion fails, it's typically due to permission issues.
    handleResponse(false, 'You are not allowed to delete this task.', null, 403);
  }
}



// COMMENT ACTIONS

/**
 * Handles adding a new comment to a task.
 * Performs validation and calls the Comment model to create the record.
 *
 * @param int $userId The ID of the user attempting to add the comment.
 * @return void Returns an API response indicating success or failure.
 */
function handleAddComment($userId)
{
  $taskId  = $_POST['task_id'] ?? 0;     // The ID of the task the comment belongs to.
  $content = trim($_POST['content'] ?? ''); // The content of the new comment.

  // Validation
  // Ensures essential fields are provided before attempting comment creation.
  if (empty($taskId) || empty($content)) {
    handleResponse(false, 'Comment cannot be empty.', null, 400);
    return;
  }

  // Prepares data array for comment creation.
  $commentData = [
    'task_id' => $taskId,
    'user_id' => $userId, // The current user is the author of the comment.
    'content' => $content
  ];

  // Attempts to create the comment.
  $newCommentId = Comment::create($commentData);

  if ($newCommentId) {
    // If creation is successful, fetch the full details of the new comment for the response.
    $comment = Comment::getById($newCommentId)->getDetails();
    handleResponse(true, 'Comment added.', ['comment' => $comment]);
  } else {
    // If comment creation fails, it's typically due to permission issues.
    handleResponse(false, 'You are not allowed to comment on this task.', null, 403);
  }
}

/**
 * Handles editing an existing comment.
 * Performs validation and calls the Comment model to update the record.
 *
 * @param int $userId The ID of the user attempting to edit the comment.
 * @return void Returns an API response indicating success or failure.
 */
function handleEditComment($userId)
{
  $commentId = $_POST['comment_id'] ?? 0;     // The ID of the comment to be edited.
  $content   = trim($_POST['content'] ?? ''); // The new content for the comment.

  $comment = Comment::getById($commentId); // Fetches the comment object.

  // Validates if the comment exists.
  if (!$comment) {
    handleResponse(false, 'Comment not found.', null, 404);
    return;
  }

  // Attempts to update the comment's content. $userId is used for permission checks.
  if ($comment->update($content, $userId)) {
    handleResponse(true, 'Comment updated.');
  } else {
    // If update fails, it's typically due to permission issues (only author or admin can edit).
    handleResponse(false, 'You can only edit your own comments (or Admin).', null, 403);
  }
}

/**
 * Handles deleting a comment.
 * Calls the Comment model to perform the deletion.
 *
 * @param int $userId The ID of the user attempting to delete the comment.
 * @return void Returns an API response indicating success or failure.
 */
function handleDeleteComment($userId)
{
  $commentId = $_POST['comment_id'] ?? 0; // The ID of the comment to be deleted.

  // Attempts to delete the comment. $userId is used for permission checks within Comment::delete.
  if (Comment::delete($commentId, $userId)) {
    handleResponse(true, 'Comment deleted successfully.');
  } else {
    // If deletion fails, it's typically due to permission issues (only author or admin can delete).
    handleResponse(false, 'You can only delete your own comments (or Admin).', null, 403);
  }
}
