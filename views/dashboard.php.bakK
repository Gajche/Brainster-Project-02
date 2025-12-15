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

<h2>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>!</h2>
<p class="lead">
    Your Role: <strong><?= htmlspecialchars($userTitle) ?></strong><br>
    Email: <strong><?= htmlspecialchars($_SESSION['user_email'] ?? 'N/A') ?></strong>
</p>

<?php if (isAdmin()): ?>
  <p><a href="<?= Config::getBaseUrl() ?>index.php?page=admin_panel" class="btn btn-secondary">Go to Admin Panel</a></p>
<?php endif; ?>

<h3>Your Projects</h3>

<?php if (empty($projects)): ?>
  <p class="alert alert-info">You are not part of any projects yet.</p>
<?php else: ?>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Title</th>
        <th>Status</th>
        <th>Deadline</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($projects as $project): ?>
        <tr>
          <td><?= htmlspecialchars($project->getTitle()) ?></td>
          <td><?= htmlspecialchars($project->getStatus()) ?></td>
          <td><?= htmlspecialchars($project->getDeadline() ? date('Y-m-d', strtotime($project->getDeadline())) : 'N/A') ?></td>
          <td>
            <?php if (isAdmin()): ?>
              Admin
            <?php elseif ($project->getTeamLeadId() == $userId): ?>
              Team Lead
            <?php else: ?>
              Member
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= Config::getBaseUrl() ?>index.php?page=project_view&id=<?= $project->getId() ?>" class="btn btn-sm btn-primary">View</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>