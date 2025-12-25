<?php
// Ensure logged in
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

require_once __DIR__ . '/../../autoload.php';

$projectId = $_GET['id'] ?? 0;
$project = Project::getById($projectId);
$userId = $_SESSION['user_id'];
$userLevel = $_SESSION['user_level'] ?? null;
$isTeamLead = $project && $project->canManageTeam($userId);

if (!$project || !$project->canAccess($userId)) {
  $_SESSION[Config::FLASH_ERROR] = 'Project not found or access denied.';
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

// Helper functions for project status
function getDaysRemaining($deadline)
{
  if (!$deadline) return null;
  $deadlineDate = strtotime($deadline);
  $today = strtotime(date('Y-m-d'));
  $diff = $deadlineDate - $today;
  return floor($diff / (60 * 60 * 24));
}

function getProjectStatusInfo($project)
{
  $status = $project->getStatus();
  $deadline = $project->getDeadline();

  if ($status === 'Done') {
    return [
      'headerClass' => 'bg-primary text-white',
      'cardClass' => 'border-primary',
      'badge' => '✓ Project Completed',
      'badgeClass' => 'bg-primary',
      'textClass' => 'text-primary',
      'icon' => '✓',
      'message' => 'This project has been completed'
    ];
  }

  $daysRemaining = getDaysRemaining($deadline);

  if ($daysRemaining === null) {
    return [
      'headerClass' => 'bg-secondary text-white',
      'cardClass' => 'border-secondary',
      'badge' => '🔄 Active Project',
      'badgeClass' => 'bg-secondary',
      'textClass' => 'text-secondary',
      'icon' => '🔄',
      'message' => 'No deadline set'
    ];
  }

  if ($daysRemaining < 0) {
    return [
      'headerClass' => 'bg-danger text-white',
      'cardClass' => 'border-danger',
      'badge' => '⚠️ Project Overdue',
      'badgeClass' => 'bg-danger',
      'textClass' => 'text-danger',
      'icon' => '⚠️',
      'message' => abs($daysRemaining) . ' days overdue'
    ];
  }

  if ($daysRemaining <= 7) {
    return [
      'headerClass' => 'bg-warning',
      'cardClass' => 'border-warning',
      'badge' => '⏰ Due Soon',
      'badgeClass' => 'bg-warning text-dark',
      'textClass' => 'text-warning',
      'icon' => '⏰',
      'message' => 'Only ' . $daysRemaining . ' days remaining'
    ];
  }

  return [
    'headerClass' => 'bg-success text-white',
    'cardClass' => 'border-success',
    'badge' => '✓ On Track',
    'badgeClass' => 'bg-success',
    'textClass' => 'text-success',
    'icon' => '✓',
    'message' => $daysRemaining . ' days remaining'
  ];
}

$statusInfo = getProjectStatusInfo($project);
$isProjectOverdue = strpos($statusInfo['badge'], 'Overdue') !== false;

$team = User::getProjectTeam($projectId);
$allUsers = User::getAll();
$availableMembers = array_filter($allUsers, function ($u) use ($team) {
  return !in_array($u['id'], array_column($team, 'id')) && $u['level'] !== 'Admin';
});

$tasks = Task::getByProject($projectId);

// Group tasks by status for Kanban view
$tasksByStatus = [];
foreach (Config::TASK_STATUSES as $status) {
  $tasksByStatus[$status] = [];
}
foreach ($tasks as $task) {
  $tasksByStatus[$task->getStatus()][] = $task;
}
?>

<h2>
  <?= htmlspecialchars($project->getTitle()) ?>
  <span class="badge <?= $statusInfo['badgeClass'] ?> ms-2"><?= $statusInfo['badge'] ?></span>
</h2>

<div class="card mb-4 shadow <?= $statusInfo['cardClass'] ?>" style="border-width: 1px;">
  <div class="card-header <?= $statusInfo['headerClass'] ?>">
    <strong>Project Details</strong>
    <span class="float-end"><?= $statusInfo['icon'] ?> <?= $statusInfo['message'] ?></span>
  </div>
  <div class="card-body">
    <p><strong>Description:</strong> <?= htmlspecialchars($project->getDescription() ?: 'N/A') ?></p>
    <p><strong>Requirements:</strong> <?= htmlspecialchars($project->getRequirements() ?: 'N/A') ?></p>
    <p><strong>Estimated Time:</strong> <?= htmlspecialchars($project->getEstimatedTime() ?: 'N/A') ?></p>
    <p>
      <strong>Deadline:</strong>
      <span class="<?= $statusInfo['textClass'] ?> fw-bold">
        <?= htmlspecialchars($project->getDeadline() ?: 'N/A') ?>
      </span>
    </p>
    <p><strong>Status:</strong>
      <span class="badge <?= $project->getStatus() === 'Done' ? 'bg-primary' : 'bg-info' ?>">
        <?= htmlspecialchars($project->getStatus()) ?>
      </span>
    </p>
    <?php if ($isTeamLead && $project->getStatus() === 'Active'): ?>
      <form method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php">
        <input type="hidden" name="action" value="mark_done">
        <input type="hidden" name="id" value="<?= $projectId ?>">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-check-circle me-2"></i> Mark as Done
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<h3>Team Members</h3>
<table class="table table-striped mb-4">
  <thead>
    <tr>
      <th class="bg-dark text-white">Name</th>
      <th class="bg-dark text-white">Level</th>
      <?php if ($isTeamLead): ?>
        <th class="bg-dark text-white">Actions</th>
      <?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($team as $member): ?>
      <tr>
        <td><?= htmlspecialchars($member['name']) ?></td>
        <td><?= htmlspecialchars($member['level']) ?></td>
        <?php if ($isTeamLead): ?>
          <td>
            <?php if ($member['id'] != $project->getTeamLeadId()): ?>
              <form class="ajax-form d-inline" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php">
                <input type="hidden" name="action" value="remove_member">
                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                <button
                  type="button"
                  class="btn btn-sm btn-danger"
                  data-confirm-delete
                  data-confirm-message="Are you sure you want to remove <strong><?= htmlspecialchars($member['name']) ?></strong> from this project?<br><br>They will lose access to all tasks and project data.">
                  <i class="fas fa-user-times me-1"></i>Remove
                </button>
              </form>
            <?php endif; ?>
          </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($isTeamLead): ?>
  <!-- Add Member Form -->
  <div class="card mb-4 shadow">
    <div class="card-header fw-bold bg-info">Add Team Member</div>
    <div class="card-body">
      <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php">
        <input type="hidden" name="action" value="add_member">
        <input type="hidden" name="project_id" value="<?= $projectId ?>">
        <div class="mb-3">
          <label for="member_id" class="form-label">Select User</label>
          <select class="form-select" id="member_id" name="member_id" required>
            <option value="">Choose...</option>
            <?php foreach ($availableMembers as $user): ?>
              <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= $user['level'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-user-plus me-2"></i> Add Member
        </button>
      </form>
    </div>
  </div>
<?php endif; ?>

<h3>Tasks</h3>

<?php if (User::getById($userId)->canCreateTask($projectId)): ?>
  <!-- Create Task Form -->
  <div class="card mb-4 shadow">
    <div class="card-header fw-bold bg-info">Create New Task</div>
    <div class="card-body">
      <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/task_controller.php">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="project_id" value="<?= $projectId ?>">
        <div class="mb-3">
          <label for="title" class="form-label">Title</label>
          <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label for="assignee_id" class="form-label">Assign To</label>
          <select class="form-select" id="assignee_id" name="assignee_id">
            <option value="">Unassigned</option>
            <?php
            $assignableUsers = User::getById($userId)->getAssignableUsers($projectId);
            foreach ($assignableUsers as $assignee): ?>
              <option value="<?= $assignee['id'] ?>"><?= htmlspecialchars($assignee['name']) ?> (<?= htmlspecialchars($assignee['level']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-tasks me-2"></i> Create Task
        </button>
      </form>
    </div>
  </div>
<?php endif; ?>

<div class="row">
  <?php foreach (Config::TASK_STATUSES as $status): ?>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="kanban-column mb-3 shadow" data-status="<?= htmlspecialchars($status) ?>">
        <div class="kanban-column-header"><?= htmlspecialchars($status) ?></div>
        <div class="kanban-cards shadow">
          <?php foreach ($tasksByStatus[$status] as $task): ?>
            <?php
            $assigned = User::getById($task->getAssignedTo());
            $assignedName = $assigned ? htmlspecialchars($assigned->getName()) : 'Unassigned';
            $creator = User::getById($task->getCreatedBy());  // New: Get creator
            $creatorName = $creator ? htmlspecialchars($creator->getName()) : 'Unknown';  // New: Creator name
            $canEditDelete = $userLevel === 'Admin' || ($userLevel === 'Senior' && User::isInProject($userId, $projectId));

            // Show overdue indicator if project is overdue and task is not Done
            $showOverdue = $isProjectOverdue && $task->getStatus() !== 'Done';
            ?>
            <div class="kanban-card <?= $showOverdue ? 'border-danger' : '' ?>" data-task-id="<?= $task->getId() ?>">
              <?php if ($showOverdue): ?>
                <span class="badge bg-danger mb-2">⚠️ OVERDUE</span>
              <?php endif; ?>
              <div class="kanban-card-title"><?= htmlspecialchars($task->getTitle()) ?></div>
              <div class="kanban-card-meta">
                <small>Assigned: <?= $assignedName ?></small><br>
                <small>Created: <?= htmlspecialchars($task->getCreatedAt()) ?></small><br>
                <small>Created by: <?= $creatorName ?></small>
              </div>
              <div class="kanban-card-actions mt-2">
                <button class="btn btn-sm btn-info view-task-btn" data-task-id="<?= $task->getId() ?>">
                  <i class="fas fa-eye me-1"></i> View
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" aria-labelledby="taskDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="taskDetailsModalTitle">Task Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="task-details-modal-body">
        <!-- Task details will be loaded here via AJAX -->
      </div>
    </div>
  </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="editTaskModalLabel">
          <i class="fas fa-edit me-2"></i> Edit Task
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="edit-task-modal-body">
        <!-- Edit form will be loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="editCommentModalLabel">
          <i class="fas fa-edit me-2"></i> Edit Comment
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="edit-comment-modal-body">
        <!-- Edit comment form will be loaded here -->
      </div>
    </div>
  </div>
</div>