<?php
// Extract data prepared by the controller
extract($_SESSION['admin_projects_data'] ?? []);
unset($_SESSION['admin_projects_data']); // Clean up session after use
?>

<h2>Manage Projects</h2>

<!-- Create Project Form -->
<div class="card mb-4">
  <h5 class="card-header">Create New Project</h5>
  <div class="card-body">
    <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php">
      <input type="hidden" name="action" value="create">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="title" class="form-label">Title</label>
          <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="col-md-4 mb-3">
          <label for="estimated_time" class="form-label">Estimated Time</label>
          <input type="text" class="form-control" id="estimated_time" name="estimated_time">
        </div>
        <div class="col-md-4 mb-3">
          <label for="deadline" class="form-label">Deadline</label>
          <input type="date" class="form-control" id="deadline" name="deadline">
        </div>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label for="requirements" class="form-label">Requirements</label>
        <textarea class="form-control" id="requirements" name="requirements" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label for="team_lead_id" class="form-label">Assign to Team Lead</label>
        <select class="form-select" id="team_lead_id" name="team_lead_id" required>
          <option value="">Select Team Lead</option>
          <?php foreach ($teamLeads as $lead): ?>
            <option value="<?= $lead['id'] ?>"><?= htmlspecialchars($lead['name']) ?> (<?= htmlspecialchars($lead['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i> Create Project
      </button>
    </form>
  </div>
</div>

<!-- Projects Table -->
<table class="table table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Title</th>
      <th>Status</th>
      <th>Deadline</th>
      <th>Team Lead</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($projects as $project): ?>
      <?php
      $lead = User::getById($project->getTeamLeadId());
      $leadName = $lead ? htmlspecialchars($lead->getName()) : 'N/A';
      ?>
      <tr>
        <td><?= $project->getId() ?></td>
        <td><?= htmlspecialchars($project->getTitle()) ?></td>
        <td><?= htmlspecialchars($project->getStatus()) ?></td>
        <td><?= htmlspecialchars($project->getDeadline() ?: 'N/A') ?></td>
        <td><?= $leadName ?></td>
        <td>
          <!-- Action buttons container with responsive gap -->
          <div class="d-inline-flex flex-wrap gap-1">
            <!-- Edit Button -->
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProjectModal<?= $project->getId() ?>">
              <i class="fas fa-edit me-1"></i> Edit
            </button>
            <!-- Delete Button Form -->
            <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $project->getId() ?>">
              <button
                type="button"
                class="btn btn-sm btn-danger"
                data-confirm-delete
                data-confirm-message="Are you sure you want to <strong>permanently delete</strong> the project<br><strong><?= htmlspecialchars($project->getTitle()) ?></strong>?<br><br>All tasks and data will be lost forever.">
                <i class="fas fa-trash-alt me-1"></i>Delete
              </button>
            </form>
          </div>
        </td>
      </tr>

      <!-- Edit Modal -->
      <div class="modal fade" id="editProjectModal<?= $project->getId() ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
              <h5 class="modal-title">
                <i class="fas fa-edit me-2"></i> Edit Project <?= htmlspecialchars($project->getTitle()) ?>
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/project_controller.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $project->getId() ?>">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label for="title<?= $project->getId() ?>" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title<?= $project->getId() ?>" name="title" value="<?= htmlspecialchars($project->getTitle()) ?>" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="estimated_time<?= $project->getId() ?>" class="form-label">Estimated Time</label>
                    <input type="text" class="form-control" id="estimated_time<?= $project->getId() ?>" name="estimated_time" value="<?= htmlspecialchars($project->getEstimatedTime()) ?>">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="deadline<?= $project->getId() ?>" class="form-label">Deadline</label>
                    <input type="date" class="form-control" id="deadline<?= $project->getId() ?>" name="deadline" value="<?= htmlspecialchars($project->getDeadline()) ?>">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="description<?= $project->getId() ?>" class="form-label">Description</label>
                  <textarea class="form-control" id="description<?= $project->getId() ?>" name="description" rows="3"><?= htmlspecialchars($project->getDescription()) ?></textarea>
                </div>
                <div class="mb-3">
                  <label for="requirements<?= $project->getId() ?>" class="form-label">Requirements</label>
                  <textarea class="form-control" id="requirements<?= $project->getId() ?>" name="requirements" rows="3"><?= htmlspecialchars($project->getRequirements()) ?></textarea>
                </div>
                <div class="mb-3">
                  <label for="team_lead_id<?= $project->getId() ?>" class="form-label">Team Lead</label>
                  <select class="form-select" id="team_lead_id<?= $project->getId() ?>" name="team_lead_id" required>
                    <?php foreach ($teamLeads as $lead): ?>
                      <option value="<?= $lead['id'] ?>" <?= $project->getTeamLeadId() == $lead['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lead['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-1"></i> Update
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </tbody>
</table>