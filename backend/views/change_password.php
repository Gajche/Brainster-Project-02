<?php
// Ensure logged in
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

// Get current user's email for password manager support
$currentUserEmail = $_SESSION['user_email'] ?? '';
// If you store username instead: $_SESSION['user_name'] ?? ''
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <h5 class="card-header bg-warning">Change Password</h5>
      <div class="card-body shadow">
        <form class="ajax-form" data-reload="true" method="POST" autocomplete="off" action="<?= Config::getBaseUrl() ?>backend/controllers/auth_controller.php">
          <input type="hidden" name="action" value="change_password">

          <!-- Hidden username field for password manager accessibility -->
          <input
            type="text"
            name="username"
            autocomplete="username"
            value="<?= htmlspecialchars($currentUserEmail) ?>"
            class="visually-hidden">

          <div class="mb-3">
            <label for="old_password" class="form-label">Old Password</label>
            <input type="password" class="form-control" id="old_password" name="old_password" autocomplete="current-password" required>
          </div>
          <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password" required>
          </div>
          <div class="mb-3">
            <label for="repeat_new_password" class="form-label">Repeat New Password</label>
            <input type="password" class="form-control" id="repeat_new_password" name="repeat_new_password" autocomplete="new-password" required>
          </div>
          <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
      </div>
    </div>
  </div>
</div>