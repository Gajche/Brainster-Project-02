<?php
extract($_SESSION['project_view_data'] ?? []);
unset($_SESSION['project_view_data']);
?>

<!-- Project Title and Status -->
<h2>
  <?= htmlspecialchars($project->getTitle()) ?>
  <span class="badge <?= $statusInfo['badgeClass'] ?> ms-2"><?= $statusInfo['badge'] ?></span>
</h2>

<!-- Project Details Card -->
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
      <form class="ajax-form d-inline" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php">
        <input type="hidden" name="action" value="mark_done">
        <input type="hidden" name="id" value="<?= $projectId ?>">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-check-circle me-2"></i> Mark as Done
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<!-- Team Members Table -->
<div class="d-flex justify-content-between align-items-center mb-2">
  <h3 class="mb-0">Team Members</h3>
  <?php if ($isTeamLead): ?>
    <div>
      <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-remove">
        <i class="fas fa-check-double me-1"></i> Select All
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-remove">
        <i class="fas fa-times me-1"></i> Clear
      </button>
    </div>
  <?php endif; ?>
</div>

<form id="remove-members-form" class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php">
  <input type="hidden" name="action" value="remove_members">
  <input type="hidden" name="project_id" value="<?= $projectId ?>">

  <table class="table table-striped mb-4">
    <thead>
      <tr>
        <?php if ($isTeamLead): ?>
          <th class="bg-dark text-white" style="width: 50px;">
            <input type="checkbox" id="select-all-checkbox-header" class="form-check-input" title="Select all removable members">
          </th>
        <?php endif; ?>
        <th class="bg-dark text-white">Name</th>
        <th class="bg-dark text-white">Level</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($team as $member): ?>
        <tr>
          <?php if ($isTeamLead): ?>
            <td>
              <?php if ($member['id'] != $project->getTeamLeadId()): ?>
                <input
                  type="checkbox"
                  class="form-check-input remove-member-checkbox"
                  name="member_ids[]"
                  value="<?= $member['id'] ?>"
                  data-member-name="<?= htmlspecialchars($member['name']) ?>">
              <?php else: ?>
                <i class="fas fa-crown text-warning" title="Team Lead cannot be removed"></i>
              <?php endif; ?>
            </td>
          <?php endif; ?>
          <td>
            <?= htmlspecialchars($member['name']) ?>
            <?php if ($member['id'] == $project->getTeamLeadId()): ?>
              <span class="badge bg-warning text-dark ms-2">Team Lead</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($member['level']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Remove button at the bottom of the form (aligned right) -->
  <?php if ($isTeamLead): ?>
    <div class="text-end">
      <button
        type="submit"
        class="btn btn-danger mb-3"
        id="remove-selected-btn"
        data-confirm-delete
        data-confirm-message="Please select members to remove."
        disabled>
        <i class="fas fa-user-times me-1"></i>
        <span id="remove-selected-text">Remove Selected (0)</span>
      </button>
    </div>
  <?php endif; ?>
</form>

<!-- Add Team Member Form -->
<?php if ($isTeamLead): ?>
  <div class="card mb-4 shadow">
    <div class="card-header fw-bold bg-dark text-white">
      Add Team Members
      <span class="badge bg-info float-end" id="selected-count">0 selected</span>
    </div>
    <div class="card-body">
      <?php if (empty($availableMembers)): ?>
        <div class="alert alert-info mb-0">
          <i class="fas fa-info-circle me-2"></i>
          All available users are already on this team.
        </div>
      <?php else: ?>
        <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php" id="add-members-form">
          <input type="hidden" name="action" value="add_members">
          <input type="hidden" name="project_id" value="<?= $projectId ?>">

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label mb-0">Select Users to Add</label>
              <div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-members">
                  <i class="fas fa-check-double me-1"></i> Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-members">
                  <i class="fas fa-times me-1"></i> Clear
                </button>
              </div>
            </div>

            <div class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
              <?php
              // Group users by level for better organization
              $usersByLevel = [
                'Senior' => [],
                'Mid' => [],
                'Junior' => []
              ];

              foreach ($availableMembers as $user) {
                $level = $user['level'] ?? 'Junior';
                if (isset($usersByLevel[$level])) {
                  $usersByLevel[$level][] = $user;
                }
              }

              // Display users grouped by level
              foreach ($usersByLevel as $level => $users):
                if (empty($users)) continue;
              ?>
                <div class="mb-3">
                  <h6 class="text-muted border-bottom pb-1">
                    <i class="fas fa-users me-1"></i> <?= $level ?> Developers (<?= count($users) ?>)
                  </h6>
                  <?php foreach ($users as $user): ?>
                    <div class="form-check">
                      <input
                        class="form-check-input member-checkbox"
                        type="checkbox"
                        name="member_ids[]"
                        value="<?= $user['id'] ?>"
                        id="member-<?= $user['id'] ?>">
                      <label class="form-check-label" for="member-<?= $user['id'] ?>">
                        <?= htmlspecialchars($user['name']) ?>
                        <span class="badge bg-secondary ms-1"><?= $user['level'] ?></span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>

            <small class="text-muted">
              <i class="fas fa-info-circle me-1"></i>
              Tip: Use Select All to quickly add multiple team members
            </small>
          </div>

          <button type="submit" class="btn btn-primary" id="add-members-btn" disabled>
            <i class="fas fa-user-plus me-2"></i>
            <span id="add-members-text">Add Selected Members</span>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<h3>Tasks</h3>

<!-- Create Task Form -->
<?php if ($canCreateTask): ?>
  <div class="card mb-4 shadow">
    <div class="card-header fw-bold bg-dark text-white">Create New Task</div>
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
            <?php foreach ($assignableUsers as $assignee): ?>
              <option value="<?= $assignee['id'] ?>">
                <?php if ($assignee['id'] == $userId): ?>👤 YOU - <?php endif; ?>
              <?= htmlspecialchars($assignee['name']) ?> (<?= htmlspecialchars($assignee['level']) ?>)
              </option>
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

<!-- Kanban Board -->
<div class="row">
  <?php foreach (Config::TASK_STATUSES as $status): ?>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="kanban-column mb-3 shadow" data-status="<?= htmlspecialchars($status) ?>">
        <div class="kanban-column-header"><?= htmlspecialchars($status) ?> (<?= count($tasksByStatus[$status]) ?>)</div>
        <div class="kanban-cards shadow">
          <?php if (empty($tasksByStatus[$status])): ?>
            <div class="text-center text-muted p-4">
              <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
              <p>No tasks in this status yet</p>
            </div>
          <?php else: ?>
            <?php foreach ($tasksByStatus[$status] as $task): ?>
              <?php
              $assigned = User::getById($task->getAssignedTo());
              $assignedName = $assigned ? htmlspecialchars($assigned->getName()) : 'Unassigned';
              $creator = User::getById($task->getCreatedBy());
              $creatorName = $creator ? htmlspecialchars($creator->getName()) : 'Unknown';
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
          <?php endif; ?>
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