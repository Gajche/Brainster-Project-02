<?php
// Redirect if already logged in
if (isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}
?>

<!-- Login View -->
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow">
      <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-0">Login</h4>
      </div>
      <div class="card-body">
        <!-- AJAX Login Form -->
        <form id="login-form" method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/auth_controller.php">
          <input type="hidden" name="action" value="login">

          <div class="mb-3">
            <label for="login" class="form-label">Name or Email</label>
            <input type="text"
              class="form-control"
              id="login"
              name="login"
              placeholder="Enter your name or email"
              autocomplete="email"
              required
              autofocus>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password"
              class="form-control"
              id="password"
              name="password"
              placeholder="Enter your password"
              autocomplete="current-password"
              required>
          </div>

          <!-- Error Message Area -->
          <div id="login-error"
            class="alert alert-danger mt-3"
            style="display: none;"
            role="alert">
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary w-100 btn-lg">
            Login
          </button>
        </form>

        <div class="text-center mt-4">
          <p class="mb-0">
            Don't have an account?
            <a href="<?= Config::getBaseUrl() ?>index.php?page=register" class="text-decoration-none fw-bold">
              Register here
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>