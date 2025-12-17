<?php
// Ensure Admin
if (!isAdmin()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}

require_once __DIR__ . '/../autoload.php';

$users = User::getAll();
?>

<h2>Manage Users</h2>

<!-- Create User Form -->
<div class="card mb-4">
  <h5 class="card-header">Create New User</h5>
  <div class="card-body">
    <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/user_controller.php">
      <input type="hidden" name="action" value="create">
      <div class="row">
        <div class="col-md-3 mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="col-md-3 mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="col-md-3 mb-3">
          <label for="level" class="form-label">Level</label>
          <select class="form-select level-select" id="level" name="level" required data-team-lead-id="is_team_lead">
            <option value="">Select</option>
            <?php foreach (Config::USER_LEVELS as $level): ?>
              <option value="<?= $level ?>"><?= $level ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 mb-3 form-check mt-4" id="team-lead-container">
          <input type="checkbox" class="form-check-input team-lead-checkbox" id="is_team_lead" name="is_team_lead">
          <label class="form-check-label" for="is_team_lead">Is Team Lead (for Seniors)</label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Create</button>
    </form>
  </div>
</div>

<!-- Users Table -->
<table class="table table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Level</th>
      <th>Team Lead</th>
      <th>Approved</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $user): ?>
      <?php
      $isAdminUser = ($user['level'] === 'Admin');
      ?>
      <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td>
          <?= htmlspecialchars($user['level']) ?>
          <?php if ($isAdminUser): ?>
            <span class="badge bg-info ms-1">System</span>
          <?php endif; ?>
        </td>
        <td><?= $user['is_team_lead'] ? 'Yes' : 'No' ?></td>
        <td><?= $user['is_approved'] ? 'Yes' : 'No' ?></td>
        <td>
          <?php if (!$user['is_approved'] && !$isAdminUser): ?>
            <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/user_controller.php" class="d-inline">
              <input type="hidden" name="action" value="approve">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <button type="submit" class="btn btn-sm btn-success">Approve</button>
            </form>
          <?php endif; ?>

          <!-- EDIT Button - Hidden for Admin users -->
          <?php if (!$isAdminUser): ?>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>">Edit</button>
          <?php else: ?>
            <button class="btn btn-sm btn-secondary" disabled title="Admin users can only be edited in database">Edit</button>
          <?php endif; ?>

          <!-- RESET PASSWORD Button - Available for all except self (handled in controller) -->
          <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/user_controller.php" class="d-inline" onsubmit="return confirm('Are you sure you want to reset the password?');">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <button type="submit" class="btn btn-sm btn-secondary">Reset Password</button>
          </form>

          <!-- DELETE Button - Hidden for Admin users and self (handled in controller) -->
          <?php if (!$isAdminUser): ?>
            <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/user_controller.php" class="d-inline" onsubmit="return confirm('Delete user?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          <?php else: ?>
            <button class="btn btn-sm btn-secondary" disabled title="Admin users can only be deleted in database">Delete</button>
          <?php endif; ?>
        </td>
      </tr>

      <!-- EDIT Modal - Only show for non-Admin users -->
      <?php if (!$isAdminUser): ?>
        <div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="editUserLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Edit User <?= htmlspecialchars($user['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/user_controller.php">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" value="<?= $user['id'] ?>">
                  <div class="mb-3">
                    <label for="name<?= $user['id'] ?>" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name<?= $user['id'] ?>" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label for="email<?= $user['id'] ?>" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email<?= $user['id'] ?>" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label for="level<?= $user['id'] ?>" class="form-label">Level</label>
                    <select class="form-select level-select" id="level<?= $user['id'] ?>" name="level" required data-team-lead-id="is_team_lead<?= $user['id'] ?>">
                      <?php foreach (Config::USER_LEVELS as $level): ?>
                        <option value="<?= $level ?>" <?= $user['level'] === $level ? 'selected' : '' ?>><?= $level ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-check mb-3 team-lead-container">
                    <input type="checkbox" class="form-check-input team-lead-checkbox" id="is_team_lead<?= $user['id'] ?>" name="is_team_lead" <?= $user['is_team_lead'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_team_lead<?= $user['id'] ?>">Is Team Lead</label>
                  </div>
                  <button type="submit" class="btn btn-primary">Update</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Note about Admin users -->
<div class="alert alert-info mt-3">
  <strong>Note:</strong> Admin users are system-level accounts and can only be managed directly in the database (not via this interface).
</div>