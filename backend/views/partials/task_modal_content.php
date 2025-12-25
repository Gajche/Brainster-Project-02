<?php
// This partial is included by task_controller.php's handleGetTaskDetailsHtml for AJAX requests
// It expects $task, $userId, $userLevel to be available.

// Ensure required models are loaded
if (!class_exists('Task')) {
  require_once __DIR__ . '/../../autoload.php';

  // require_once Config::ROOT_DIR . '/models/Task.php';
  // require_once Config::ROOT_DIR . '/models/Comment.php';
  // require_once Config::ROOT_DIR . '/models/User.php';
}


// Setup variables and permissions
$assigned = User::getById($task->getAssignedTo());
$assignedName = $assigned ? htmlspecialchars($assigned->getName()) : 'Unassigned';
$comments = Comment::getByTask($task->getId());
$currentUser = User::getById($userId);

// More granular permission checks for the UI
$isTeamLeadOfProject = User::isProjectLead($userId, $task->getProjectId());
$canComment = $currentUser->canCommentOnTask($task->getId());
$canChangeStatus = $currentUser->canChangeTaskStatus($task->getId());

$canEditTask = $currentUser->canEditTask($task->getId());
$canDeleteTask = isAdmin() || $isTeamLeadOfProject;

// Determine if the assign/unassign controls should be visible at all
$canAssign = false;
$currentAssignee = $task->getAssignedTo() ? User::getById($task->getAssignedTo()) : null;

if (isAdmin() || $isTeamLeadOfProject) {
  $canAssign = true;
} elseif ($currentUser->getLevel() === 'Senior') {
  // A Senior can assign tasks that are unassigned, or assigned to themselves, Mid, or Junior.
  if (!$currentAssignee || in_array($currentAssignee->getLevel(), ['Senior', 'Mid', 'Junior'])) {
    $canAssign = true;
  }
} elseif ($currentUser->getLevel() === 'Mid') {
  // A Mid can assign tasks that are unassigned, or assigned to themselves or a Junior.
  if (!$currentAssignee || in_array($currentAssignee->getLevel(), ['Mid', 'Junior'])) {
    $canAssign = true;
  }
}
$canUnassign = $canAssign && $currentAssignee;
// 
?>

<p><strong>Description:</strong> <?= htmlspecialchars($task->getDescription() ?: 'N/A') ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($task->getStatus()) ?></p>
<p><strong>Assigned To:</strong> <?= $assignedName ?></p>
<p><strong>Created At:</strong> <?= htmlspecialchars($task->getCreatedAt()) ?></p>

<?php if ($canChangeStatus): ?>
  <div class="mt-3">
    <form class="ajax-form d-inline-block" data-reload="true" data-task-id="<?= $task->getId() ?>" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
      <input type="hidden" name="action" value="change_status">
      <input type="hidden" name="task_id" value="<?= $task->getId() ?>">
      <label for="status-<?= $task->getId() ?>" class="form-label">Change Status</label>
      <select class="form-select d-inline-block w-auto" id="status-<?= $task->getId() ?>" name="status" required onchange="this.form.submit()">
        <?php foreach (Config::TASK_STATUSES as $statusOption): ?>
          <option value="<?= $statusOption ?>" <?= $task->getStatus() === $statusOption ? 'selected' : '' ?>><?= $statusOption ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
<?php endif; ?>

<?php if ($canAssign || $canUnassign || $canEditTask || $canDeleteTask): ?>
  <div class="mt-3 d-flex flex-wrap gap-2 align-items-end">
    <?php if ($canAssign): ?>
      <form class="ajax-form flex-grow-1" data-reload="task-modal" data-task-id="<?= $task->getId() ?>" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
        <input type="hidden" name="action" value="assign">
        <input type="hidden" name="task_id" value="<?= $task->getId() ?>">
        <label for="assignee_id-<?= $task->getId() ?>" class="form-label mb-1">Reassign To</label>
        <select class="form-select" id="assignee_id-<?= $task->getId() ?>" name="assignee_id" required onchange="this.form.submit()">
          <option value="">Select Assignee</option>
          <?php foreach ($currentUser->getAssignableUsers($task->getProjectId()) as $assigneeOption): ?>
            <option value="<?= $assigneeOption['id'] ?>" <?= $task->getAssignedTo() == $assigneeOption['id'] ? 'selected' : '' ?>><?= htmlspecialchars($assigneeOption['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    <?php endif; ?>

    <div class="d-flex gap-2">
      <?php if ($canUnassign): ?>
        <form class="ajax-form" data-reload="task-modal" data-task-id="<?= $task->getId() ?>" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
          <input type="hidden" name="action" value="unassign">
          <input type="hidden" name="task_id" value="<?= $task->getId() ?>">
          <button type="submit" class="btn btn-secondary">
            <i class="fas fa-user-times me-1"></i> Unassign
          </button>
        </form>
      <?php endif; ?>

      <?php if ($canEditTask): ?>
        <button class="btn btn-sm btn-warning edit-task-btn" data-task-id="<?= $task->getId() ?>">
          <i class="fas fa-edit me-1"></i> Edit Task
        </button>
      <?php endif; ?>

      <?php if ($canDeleteTask): ?>
        <form class="ajax-form d-inline" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="task_id" value="<?= $task->getId() ?>">
          <button
            type="button"
            class="btn btn-danger"
            data-confirm-delete
            data-confirm-message="Are you sure you want to <strong>permanently delete</strong> the task:<br><br><strong><?= htmlspecialchars($task->getTitle()) ?></strong>?<br><br>All comments and history will be lost forever.">
            <i class="fas fa-trash-alt me-1"></i> Delete Task
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<h4 class="mt-4">Comments</h4>
<?php if ($canComment): ?>
  <div class="card mb-3">
    <div class="card-body">
      <form id="add-comment-form-modal" class="ajax-form" data-reload="task-modal" data-task-id="<?= $task->getId() ?>" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
        <input type="hidden" name="action" value="add_comment">
        <input type="hidden" name="task_id" value="<?= $task->getId() ?>">
        <div class="mb-3">
          <textarea class="form-control" name="content" rows="2" required placeholder="Add a comment..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fas fa-paper-plane me-1"></i> Post Comment
        </button>
      </form>
      <div id="comment-error-modal-<?= $task->getId() ?>" class="alert alert-danger mt-3" style="display:none;"></div>
    </div>
  </div>
<?php endif; ?>

<div id="comments-list-modal-<?= $task->getId() ?>">
  <?php if (empty($comments)): ?>
    <p class="alert alert-info">No comments yet.</p>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($comments as $comment): ?>
        <div class="list-group-item list-group-item-action flex-column align-items-start">
          <div class="d-flex w-100 justify-content-between">
            <h6 class="mb-1"><strong><?= htmlspecialchars($comment['user_name']) ?></strong></h6>
            <small><?= htmlspecialchars($comment['created_at']) ?> <?php if ($comment['edited']): ?>(Edited)<?php endif; ?></small>
          </div>
          <p class="mb-1"><em><?= htmlspecialchars($comment['content']) ?></em></p>
          <?php if ($comment['user_id'] == $userId || isAdmin()): ?>
            <div class="d-flex justify-content-end mt-2">
              <button class="btn btn-sm btn-outline-warning me-2 edit-comment-btn" data-comment-id="<?= $comment['id'] ?>">
                <i class="fas fa-edit me-1"></i> Edit
              </button>
              <form class="ajax-form d-inline" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
                <input type="hidden" name="action" value="delete_comment">
                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-danger"
                  data-confirm-delete
                  data-confirm-message="Are you sure you want to <strong>permanently delete</strong> this comment?<br><br><em><?= htmlspecialchars(substr($comment['content'], 0, 100)) ?><?= strlen($comment['content']) > 100 ? '...' : '' ?></em><br><br>by <strong><?= htmlspecialchars($comment['user_name']) ?></strong>">
                  <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>