<?php
// Redirect if already logged in
if (isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <h4 class="card-header bg-primary text-white text-center">Register</h4>
      <div class="card-body">
        <form method="POST" action="<?= Config::getBaseUrl() ?>controllers/auth_controller.php">
          <input type="hidden" name="action" value="register">
          <div class="mb-3">
            <label for="name" class="form-label">Name/Nickname</label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="mb-3">
            <label for="repeat_password" class="form-label">Repeat Password</label>
            <input type="password" class="form-control" id="repeat_password" name="repeat_password" required>
          </div>
          <div class="mb-3">
            <label for="level" class="form-label">Level</label>
            <select class="form-select" id="level" name="level" required>
              <option value="">Select Level</option>
              <option value="Senior">Senior</option>
              <option value="Mid">Mid</option>
              <option value="Junior">Junior</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100 btn-lg">Register</button>
        </form>
        <p class="mt-3">Already have an account? <a href="<?= Config::getBaseUrl() ?>index.php?page=login" class="text-decoration-none fw-bold">Login here</a>.</p>
      </div>
    </div>
  </div>
</div>