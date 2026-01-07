<?php
// Get current page from URL, default to 'dashboard' if logged in
$currentPage = $_GET['page'] ?? (isLoggedIn() ? 'dashboard' : 'login');

// Function to check if a nav item is active
function isActive($pageName)
{
  global $currentPage;
  return $currentPage === $pageName ? 'active' : '';
}
?>

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
            <a class="nav-link <?= isActive('dashboard') ?>" href="<?= Config::getBaseUrl() ?>index.php?page=dashboard">Dashboard</a>
          </li>
          <?php if (isAdmin()): ?>
            <li class="nav-item">
              <a class="nav-link <?= isActive('admin_panel') ?>" href="<?= Config::getBaseUrl() ?>index.php?page=admin_panel">Admin Panel</a>
            </li>
            <!-- Optional: Direct links to admin sections -->
            <li class="nav-item">
              <a class="nav-link <?= isActive('admin_users') ?>" href="<?= Config::getBaseUrl() ?>index.php?page=admin_users">Users</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= isActive('admin_projects') ?>" href="<?= Config::getBaseUrl() ?>index.php?page=admin_projects">Projects</a>
            </li>

          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link <?= isActive('change_password') ?>" href="<?= Config::getBaseUrl() ?>index.php?page=change_password">Change Password</a>
          </li>
          <li class="nav-item">
            <!-- Logout form -->
            <form method="POST" action="<?= Config::getBaseUrl() ?>backend/controllers/auth_controller.php" class="d-inline">
              <input type="hidden" name="action" value="logout">
              <button type="submit" class="nav-link border-0 bg-transparent" style="cursor: pointer;">
                Logout
              </button>
            </form>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link <?= isActive('login') ?>" href="<?= Config::getBaseUrl() ?>index.php?page=login">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= isActive('register') ?>" href="<?= Config::getBaseUrl() ?>index.php?page=register">Register</a>
          </li>
        <?php endif; ?>
      </ul>

      <!-- User info (desktop only) -->
      <?php if (isLoggedIn()): ?>
        <div class="navbar-text ms-3 d-none d-md-block">
          <i class="fas fa-user me-1"></i>
          <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
          <span class="badge bg-info ms-1"><?= $_SESSION['user_level'] ?? 'User' ?></span>
          <?php if (($_SESSION['user_level'] ?? '') === 'Senior' && ($_SESSION['is_team_lead'] ?? false)): ?>
            <span class="badge bg-warning ms-1">Team Lead</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>