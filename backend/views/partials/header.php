<nav class="navbar navbar-expand-lg navbar-dark bg-dark white-text">
  <div class="container">
    <a class="navbar-brand" href="<?= Config::getBaseUrl() ?>index.php?page=dashboard"><?= Config::SITE_TITLE ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isLoggedIn()): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= Config::getBaseUrl() ?>index.php?page=dashboard">Dashboard</a>
          </li>
          <?php if (isAdmin()): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= Config::getBaseUrl() ?>index.php?page=admin_panel">Admin Panel</a>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= Config::getBaseUrl() ?>index.php?page=change_password">Change Password</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Config::getBaseUrl() ?>backend/controllers/auth_controller.php?action=logout">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= Config::getBaseUrl() ?>index.php?page=login">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= Config::getBaseUrl() ?>index.php?page=register">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>