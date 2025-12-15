<?php
// Ensure logged in
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <h5 class="card-header bg-warning">Change Password</h5>
      <div class="card-body shadow">
        <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/auth_controller.php">
          <input type="hidden" name="action" value="change_password">
          <div class="mb-3">
            <label for="old_password" class="form-label">Old Password</label>
            <input type="password" class="form-control" id="old_password" name="old_password" required>
          </div>
          <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
          </div>
          <div class="mb-3">
            <label for="repeat_new_password" class="form-label">Repeat New Password</label>
            <input type="password" class="form-control" id="repeat_new_password" name="repeat_new_password" required>
          </div>
          <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
      </div>
    </div>
  </div>
</div>