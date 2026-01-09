<?php
// Extract data prepared by the controller
extract($_SESSION['admin_users_data'] ?? []);
unset($_SESSION['admin_users_data']); // Clean up session after use
?>

<h2>Manage Users</h2>

<!-- Create User Form -->
<div class="card mb-4">
  <h5 class="card-header">Create New User</h5>
  <div class="card-body">
    <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/user_controller.php">
      <input type="hidden" name="action" value="create">
      <div class="row">
        <!-- Stack columns vertically on small screens -->
        <div class="col-12 col-md-3 mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" class="form-control" id="name" name="name" autocomplete="name" required>
        </div>
        <div class="col-12 col-md-3 mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
        </div>
        <div class="col-12 col-md-3 mb-3">
          <label for="level" class="form-label">Level</label>
          <select class="form-select level-select" id="level" name="level" required data-team-lead-id="is_team_lead">
            <option value="">Select</option>
            <?php foreach ($levels as $level): ?>
              <option value="<?= $level ?>"><?= $level ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-3 mb-3">
          <!-- Move checkbox out of form-check div for better Bootstrap 5 mobile handling -->
          <div class="mt-3 mt-md-4 pt-1">
            <div class="form-check">
              <input type="checkbox" class="form-check-input team-lead-checkbox" id="is_team_lead" name="is_team_lead">
              <label class="form-check-label" for="is_team_lead">Is Team Lead (for Seniors)</label>
            </div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i> Create User
      </button>
    </form>
  </div>
</div>

<!-- Desktop Table (hidden on mobile) -->
<div class="d-none d-md-block">
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
            <div class="d-flex flex-wrap gap-1">
              <?php if (!$user['is_approved'] && !$isAdminUser): ?>
                <form class="ajax-form d-inline" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/user_controller.php">
                  <input type="hidden" name="action" value="approve">
                  <input type="hidden" name="id" value="<?= $user['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-success">Approve</button>
                </form>
              <?php endif; ?>

              <!-- EDIT Button - Hidden for Admin users -->
              <?php if (!$isAdminUser): ?>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>">
                  <i class="fas fa-edit me-1"></i> Edit
                </button>
              <?php else: ?>
                <button class="btn btn-sm btn-secondary" disabled title="Admin users can only be edited in database">Edit</button>
              <?php endif; ?>

              <!-- RESET PASSWORD Button -->
              <form class="ajax-form d-inline" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/user_controller.php">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <button
                  type="button"
                  class="btn btn-sm btn-secondary"
                  data-confirm-delete
                  data-confirm-type="reset-password"
                  data-confirm-message="Are you sure you want to reset the password for:<br><br><strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)?<br><br>A new random password will be generated and shown once.">
                  <i class="fas fa-key me-1"></i> Reset Password
                </button>
              </form>

              <!-- DELETE Button - Hidden for Admin users and self -->
              <?php if (!$isAdminUser): ?>
                <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/user_controller.php" class="d-inline">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $user['id'] ?>">
                  <button
                    type="button"
                    class="btn btn-sm btn-danger"
                    data-confirm-delete
                    data-confirm-message="Are you sure you want to <strong>permanently delete</strong> the user:<br><br><strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)?<br><br>All their tasks, comments, and data will be lost.">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                  </button>
                </form>
              <?php else: ?>
                <button class="btn btn-sm btn-secondary" disabled title="Admin users can only be deleted in database">Delete</button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Mobile Cards (hidden on desktop) -->
<div class="d-md-none">
  <?php foreach ($users as $user): ?>
    <?php
    $isAdminUser = ($user['level'] === 'Admin');
    ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="row mb-2">
          <div class="col-6 fw-bold">ID:</div>
          <div class="col-6"><?= $user['id'] ?></div>
        </div>
        <div class="row mb-2">
          <div class="col-6 fw-bold">Name:</div>
          <div class="col-6"><?= htmlspecialchars($user['name']) ?></div>
        </div>
        <div class="row mb-2">
          <div class="col-6 fw-bold">Email:</div>
          <div class="col-6"><?= htmlspecialchars($user['email']) ?></div>
        </div>
        <div class="row mb-2">
          <div class="col-6 fw-bold">Level:</div>
          <div class="col-6">
            <?= htmlspecialchars($user['level']) ?>
            <?php if ($isAdminUser): ?>
              <span class="badge bg-info ms-1">System</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col-6 fw-bold">Team Lead:</div>
          <div class="col-6"><?= $user['is_team_lead'] ? 'Yes' : 'No' ?></div>
        </div>
        <div class="row mb-3">
          <div class="col-6 fw-bold">Approved:</div>
          <div class="col-6"><?= $user['is_approved'] ? 'Yes' : 'No' ?></div>
        </div>

        <!-- Action Buttons for Mobile -->
        <div class="d-flex flex-wrap gap-2">
          <?php if (!$user['is_approved'] && !$isAdminUser): ?>
            <form class="ajax-form d-inline" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/user_controller.php">
              <input type="hidden" name="action" value="approve">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <button type="submit" class="btn btn-sm btn-success">Approve</button>
            </form>
          <?php endif; ?>

          <!-- EDIT Button - Hidden for Admin users -->
          <?php if (!$isAdminUser): ?>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>">
              <i class="fas fa-edit me-1"></i> Edit
            </button>
          <?php else: ?>
            <button class="btn btn-sm btn-secondary" disabled title="Admin users can only be edited in database">Edit</button>
          <?php endif; ?>

          <!-- RESET PASSWORD Button -->
          <form class="ajax-form d-inline" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/user_controller.php">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <button
              type="button"
              class="btn btn-sm btn-secondary"
              data-confirm-delete
              data-confirm-type="reset-password"
              data-confirm-message="Are you sure you want to reset the password for:<br><br><strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)?<br><br>A new random password will be generated and shown once.">
              <i class="fas fa-key me-1"></i> Reset Password
            </button>
          </form>

          <!-- DELETE Button - Hidden for Admin users and self -->
          <?php if (!$isAdminUser): ?>
            <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/user_controller.php">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <button
                type="button"
                class="btn btn-sm btn-danger"
                data-confirm-delete
                data-confirm-message="Are you sure you want to <strong>permanently delete</strong> the user:<br><br><strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)?<br><br>All their tasks, comments, and data will be lost.">
                <i class="fas fa-trash-alt me-1"></i> Delete
              </button>
            </form>
          <?php else: ?>
            <button class="btn btn-sm btn-secondary" disabled title="Admin users can only be deleted in database">Delete</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- EDIT Modals (outside both table and cards) -->
<?php foreach ($users as $user): ?>
  <?php if ($user['level'] !== 'Admin'): ?>
    <div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="editUserLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="editUserLabel">
              <i class="fas fa-edit me-2"></i> Edit User <?= htmlspecialchars($user['name']) ?>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form class="ajax-form" data-reload="true" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/user_controller.php">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <div class="mb-3">
                <label for="name<?= $user['id'] ?>" class="form-label">Name</label>
                <input type="text" class="form-control" id="name<?= $user['id'] ?>" name="name" autocomplete="name" value="<?= htmlspecialchars($user['name']) ?>" required>
              </div>
              <div class="mb-3">
                <label for="email<?= $user['id'] ?>" class="form-label">Email</label>
                <input type="email" class="form-control" id="email<?= $user['id'] ?>" autocomplete="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
              </div>
              <div class="mb-3">
                <label for="level<?= $user['id'] ?>" class="form-label">Level</label>
                <select class="form-select level-select" id="level<?= $user['id'] ?>" name="level" required data-team-lead-id="is_team_lead<?= $user['id'] ?>">
                  <?php foreach ($levels as $level): ?>
                    <option value="<?= $level ?>" <?= $user['level'] === $level ? 'selected' : '' ?>><?= $level ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-check mb-3 team-lead-container">
                <input type="checkbox" class="form-check-input team-lead-checkbox" id="is_team_lead<?= $user['id'] ?>" name="is_team_lead" <?= $user['is_team_lead'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_team_lead<?= $user['id'] ?>">Is Team Lead</label>
              </div>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Update
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<!-- Note about Admin users -->
<div class="alert alert-info mt-3">
  <strong>Note:</strong> Admin users are system-level accounts and can only be managed directly in the database (not via this interface).
</div>