<?php
// Redirect if already logged in (though index.php defaults to dashboard)
if (isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Login</div>
      <div class="card-body">
        <form id="login-form" method="POST" action="<?= Config::getBaseUrl() ?>controllers/auth_controller.php">
          <input type="hidden" name="action" value="login">
          <div class="mb-3">
            <label for="login" class="form-label">Name or Email</label>
            <input type="text" class="form-control" id="login" name="login" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div id="login-error" class="alert alert-danger mt-3" style="display:none;"></div>
        <p class="mt-3">Don't have an account? <a href="<?= Config::getBaseUrl() ?>index.php?page=register">Register here</a>.</p>
      </div>
    </div>
  </div>
</div>