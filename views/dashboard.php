<?php
// Ensure logged in
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

require_once Config::ROOT_DIR . '/models/Project.php';

$userId = $_SESSION['user_id'];
// If user is Admin, get all projects. Otherwise, get only their projects.
if (isAdmin()) {
  $projects = Project::getAll();
} else {
  $projects = Project::getForUser($userId);
}
?>

<?php
// Determine the user's full title
$userTitle = $_SESSION['user_level'] ?? 'User';
if (($_SESSION['user_level'] ?? '') === 'Senior' && ($_SESSION['is_team_lead'] ?? false)) {
  $userTitle = 'Team Lead Senior';
}
?>

<h3>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>!</h3>
<p class="lead">
  Your Role: <strong><?= htmlspecialchars($userTitle) ?></strong><br>
  Email: <strong><?= htmlspecialchars($_SESSION['user_email'] ?? 'N/A') ?></strong>
</p>

<hr>

<?php if (isAdmin()): ?>
  <p><a href="<?= Config::getBaseUrl() ?>index.php?page=admin_panel" class="btn btn-secondary">Go to Admin Panel</a></p>
<?php endif; ?>

<h3>Your Projects</h3>

<?php if (empty($projects)): ?>
  <p class="alert alert-info">You are not part of any projects yet.</p>
<?php else: ?>
  <div class="row">
    <?php foreach ($projects as $project): ?>
      <div class="col-md-3 mb-4">
        <div class="card h-100">
          <div class="card-body bg-lightgray shadow">
            <h5 class="card-title"><?= htmlspecialchars($project->getTitle()) ?></h5>
            <p class="card-text">
              <strong>Status:</strong> <?= htmlspecialchars($project->getStatus()) ?><br>
              <strong>Deadline:</strong> <?= htmlspecialchars($project->getDeadline() ? date('Y-m-d', strtotime($project->getDeadline())) : 'N/A') ?><br>
              <strong>Role:</strong>
              <?php if (isAdmin()): ?>
                Admin
              <?php elseif ($project->getTeamLeadId() == $userId): ?>
                Team Lead
              <?php else: ?>
                Member
              <?php endif; ?>
            </p>
            <a href="<?= Config::getBaseUrl() ?>index.php?page=project_view&id=<?= $project->getId() ?>" class="btn btn-sm btn-primary">View</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <hr>
<?php endif; ?>