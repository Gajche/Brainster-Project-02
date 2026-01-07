<?php

require_once __DIR__ . '/bootstrap/task_view_context.php';

?>


<!-- Task Details -->
<h2>Task: <?= htmlspecialchars($task->getTitle()) ?></h2>

<div class="card mb-4">
  <div class="card-header">Task Details</div>
  <div class="card-body">
    <p><strong>Description:</strong> <?= htmlspecialchars($task->getDescription() ?: 'N/A') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($task->getStatus()) ?></p>
    <p><strong>Assigned To:</strong> <?= $assignedName ?></p>
    <p><strong>Created At:</strong> <?= htmlspecialchars($task->getCreatedAt()) ?></p>

    <?php if ($canChangeStatus): ?>
      <!-- Status Change Form - Now AJAX -->
      <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
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
      <div class="mt-3 d-flex gap-2 align-items-end">
        <?php if ($canAssign): ?>
          <!-- Assign Form - Now AJAX -->
          <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php" class="mb-0">
            <input type="hidden" name="action" value="assign">
            <input type="hidden" name="task_id" value="<?= $taskId ?>">
            <div class="mb-2">
              <label for="assignee_id" class="form-label">Reassign To</label>
              <select class="form-select" id="assignee_id" name="assignee_id" required>
                <option value="">Select Assignee</option>
                <?php foreach (User::getById($userId)->getAssignableUsers($task->getProjectId()) as $assignee): ?>
                  <option value="<?= $assignee['id'] ?>" <?= $task->getAssignedTo() == $assignee['id'] ? 'selected' : '' ?>><?= htmlspecialchars($assignee['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Assign</button>
          </form>
        <?php endif; ?>

        <?php if ($canAssign && $task->getAssignedTo() && $userLevel !== 'Mid'): ?>
          <!-- Unassign Form - Now AJAX -->
          <!-- Mid users cannot unassign tasks -->
          <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php" class="mb-0">
            <input type="hidden" name="action" value="unassign">
            <input type="hidden" name="task_id" value="<?= $taskId ?>">
            <button type="submit" class="btn btn-secondary">Unassign</button>
          </form>
        <?php endif; ?>

        <?php if ($canEditTask): ?>
          <div class="d-flex gap-2 mt-3">
            <!-- Edit Button (opens modal) -->
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editTaskModal">Edit Task</button>

            <!-- Delete Form - Now AJAX with proper confirmation -->
            <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="task_id" value="<?= $taskId ?>">
              <button
                type="button"
                class="btn btn-danger"
                data-confirm-delete
                data-confirm-message="Are you sure you want to <strong>permanently delete</strong> the task:<br><br><strong><?= htmlspecialchars($task->getTitle()) ?></strong>?<br><br>All comments and history will be lost forever.">
                Delete Task
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Edit Task Modal -->
<?php if ($canEditTask): ?>
  <div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Edit Form - Now AJAX -->
          <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="task_id" value="<?= $taskId ?>">
            <div class="mb-3">
              <label for="title" class="form-label">Title</label>
              <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($task->getTitle()) ?>" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($task->getDescription()) ?></textarea>
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
  <!-- Add Comment Form - Now AJAX -->
  <div class="card mb-4">
    <div class="card-header">Add Comment</div>
    <div class="card-body">
      <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
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
            <!-- Edit Button (unchanged) -->
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCommentModal<?= $comment['id'] ?>">Edit</button>

            <!-- Delete Comment Form - Now AJAX -->
            <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php" class="d-inline">
              <input type="hidden" name="action" value="delete_comment">
              <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
              <button
                type="button"
                class="btn btn-sm btn-danger"
                data-confirm-delete
                data-confirm-message="Are you sure you want to <strong>permanently delete</strong> this comment?<br><br><em><?= htmlspecialchars(substr($comment['content'], 0, 100)) ?><?= strlen($comment['content']) > 100 ? '...' : '' ?></em><br><br>by <strong><?= htmlspecialchars($comment['user_name']) ?></strong>">
                Delete
              </button>
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
                <!-- Edit Comment Form - Now AJAX -->
                <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
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