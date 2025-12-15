<?php
// Ensure Admin
if (!isAdmin()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=dashboard');
  exit;
}
?>

<h2>Admin Panel</h2>

<div class="row">
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body p-4 bg-lightgray shadow">
        <h5 class="card-title">Manage Users</h5>
        <p>Create, edit, approve, and delete user accounts.</p>
        <a href="<?= Config::getBaseUrl() ?>index.php?page=admin_users" class="btn btn-primary">Go to Users</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body p-4 bg-lightgray shadow">
        <h5 class="card-title">Manage Projects</h5>
        <p>Create, edit, assign, and delete projects.</p>
        <a href="<?= Config::getBaseUrl() ?>index.php?page=admin_projects" class="btn btn-primary">Go to Projects</a>
      </div>
    </div>
  </div>
</div>