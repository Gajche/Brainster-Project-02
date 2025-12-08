<?php
// Ensure logged in
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

require_once Config::ROOT_DIR . '/models/Task.php';
require_once Config::ROOT_DIR . '/models/Comment.php';
require_once Config::ROOT_DIR . '/models/User.php';

$taskId = $_GET['id'] ?? 0;
$task = Task::getById($taskId);
$userId = $_SESSION['user_id'];
$user = User::getById($userId);
$userLevel = $_SESSION['user_level'] ?? null;

if (!$task || (!isAdmin() && !$user->isInProject($userId, $task->getProjectId()))) {
  $_SESSION[Config::FLASH_ERROR] = 'Task not found or access denied.';
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

$assigned = User::getById($task->getAssignedTo());
$assignedName = $assigned ? htmlspecialchars($assigned->getName()) : 'Unassigned';

$comments = $task->getComments();
$canComment = $user->canCommentOnTask($taskId);
$canChangeStatus = $user->canChangeTaskStatus($taskId);
$isTeamLead = User::isProjectLead($userId, $task->getProjectId());
$canAssign = isAdmin() || $isTeamLead || !empty($user->getAssignableUsers($task->getProjectId()));
$canEditTask = $userLevel === 'Admin' || ($userLevel === 'Senior' && User::isInProject($userId, $task->getProjectId()));
?>

<h2>Task: <?= htmlspecialchars($task->getTitle()) ?></h2>

<div class="card mb-4">
  <div class="card-header">Task Details</div>
  <div class="card-body">
    <p><strong>Description:</strong> <?= htmlspecialchars($task->getDescription() ?: 'N/A') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($task->getStatus()) ?></p>
    <p><strong>Assigned To:</strong> <?= $assignedName ?></p>
    <p><strong>Created At:</strong> <?= htmlspecialchars($task->getCreatedAt()) ?></p>

    <?php if ($canChangeStatus): ?>
      <!-- Change Status Form -->
      <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/task_controller.php">
        <input type="hidden" name="action" value="change_status">
        <input type="hidden" name="task_id" value="<?= $taskId ?>">
        <div class="mb-3">
          <label for="status" class="form-label">Change Status</label>
          <select class="form-select" id="status" name="status" required>
            <?php foreach (Config::TASK_STATUSES as $status): ?>
              <option value="<?= $status ?>" <?= $task->getStatus() === $status ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Status</button>
      </form>
    <?php endif; ?>

    <?php if ($canAssign || $canEditTask): ?>
      <!-- Assignment / Edit Actions -->
      <div class="mt-3 d-flex gap-2 align-items-end">
        <?php if ($canAssign): ?>
          <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/task_controller.php" class="mb-0">
            <input type="hidden" name="action" value="assign">
            <input type="hidden" name="task_id" value="<?= $taskId ?>">
            <div class="mb-2">
              <label for="assignee_id" class="form-label">Reassign To</label>
              <select class="form-select" id="assignee_id" name="assignee_id" required>
                <option value="">Select Assignee</option>
                <?php foreach ($user->getAssignableUsers($task->getProjectId()) as $assignee): ?>
                  <option value="<?= $assignee['id'] ?>" <?= $task->getAssignedTo() == $assignee['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($assignee['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Assign</button>
          </form>
        <?php endif; ?>

        <?php if ($canAssign && $task->getAssignedTo()): ?>
          <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/task_controller.php" class="mb-0">
            <input type="hidden" name="action" value="unassign">
            <input type="hidden" name="task_id" value="<?= $taskId ?>">
            <button type="submit" class="btn btn-secondary">Unassign</button>
          </form>
        <?php endif; ?>

        <?php if ($canEditTask): ?>
          <div class="d-flex gap-2 mt-3">
            <!-- Edit Task Button triggers modal -->
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editTaskModal<?= $taskId ?>">Edit Task</button>

            <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/task_controller.php" onsubmit="return confirm('Delete this task?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="task_id" value="<?= $taskId ?>">
              <button type="submit" class="btn btn-danger">Delete Task</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Edit Task Modal -->
<?php if ($canEditTask): ?>
  <div class="modal fade" id="editTaskModal<?= $taskId ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/task_controller.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="task_id" value="<?= $taskId ?>">
            <div class="mb-3">
              <label for="title<?= $taskId ?>" class="form-label">Title</label>
              <input type="text" class="form-control" id="title<?= $taskId ?>" name="title" value="<?= htmlspecialchars($task->getTitle()) ?>" required>
            </div>
            <div class="mb-3">
              <label for="description<?= $taskId ?>" class="form-label">Description</label>
              <textarea class="form-control" id="description<?= $taskId ?>" name="description" rows="3"><?= htmlspecialchars($task->getDescription()) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<h3>Comments</h3>

<?php if ($canComment): ?>
  <!-- Add Comment Form -->
  <div class="card mb-4">
    <div class="card-header">Add Comment</div>
    <div class="card-body">
      <form id="add-comment-form" method="POST" action="<?= Config::getBaseUrl() ?>controllers/task_controller.php">
        <input type="hidden" name="action" value="add_comment">
        <input type="hidden" name="task_id" value="<?= $taskId ?>">
        <div class="mb-3">
          <textarea class="form-control" name="content" rows="3" required placeholder="Type your comment here..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Comment</button>
      </form>
      <div id="comment-error" class="alert alert-danger mt-3" style="display:none;"></div>
    </div>
  </div>
<?php endif; ?>

<!-- Comments List -->
<div id="comments-list">
  <?php if (empty($comments)): ?>
    <p class="alert alert-info">No comments yet.</p>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($comments as $comment): ?>
        <div class="list-group-item">
          <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><?= htmlspecialchars($comment['user_name']) ?></h5>
            <small><?= htmlspecialchars($comment['created_at']) ?> <?php if ($comment['edited']): ?>(Edited)<?php endif; ?></small>
          </div>
          <p class="mb-1"><?= htmlspecialchars($comment['content']) ?></p>
          <?php if ($comment['user_id'] == $userId || $_SESSION['user_level'] === 'Admin'): ?>
            <!-- Edit Button triggers modal -->
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCommentModal<?= $comment['id'] ?>">Edit</button>

            <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/task_controller.php" class="d-inline" onsubmit="return confirm('Delete comment?');">
              <input type="hidden" name="action" value="delete_comment">
              <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          <?php endif; ?>
        </div>

        <!-- Edit Comment Modal -->
        <div class="modal fade" id="editCommentModal<?= $comment['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Edit Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/task_controller.php">
                  <input type="hidden" name="action" value="edit_comment">
                  <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                  <div class="mb-3">
                    <textarea class="form-control" name="content" rows="3" required><?= htmlspecialchars($comment['content']) ?></textarea>
                  </div>
                  <button type="submit" class="btn btn-primary">Update</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>