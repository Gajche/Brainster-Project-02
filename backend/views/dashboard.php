<?php
// Ensure logged in
if (!isLoggedIn()) {
  header('Location: ' . Config::getBaseUrl() . 'index.php?page=login');
  exit;
}

require_once __DIR__ . '/../../autoload.php';

$userId = $_SESSION['user_id'];
// If user is Admin, get all projects. Otherwise, get only their projects.
if (isAdmin()) {
  $projects = Project::getAll();
} else {
  $projects = Project::getForUser($userId);
}

// Helper function to check if project is overdue
function isProjectOverdue($deadline)
{
  if (!$deadline) return false;
  $deadlineDate = strtotime($deadline);
  $today = strtotime(date('Y-m-d'));
  return $deadlineDate < $today;
}

// Helper function to get days remaining
function getDaysRemaining($deadline)
{
  if (!$deadline) return null;
  $deadlineDate = strtotime($deadline);
  $today = strtotime(date('Y-m-d'));
  $diff = $deadlineDate - $today;
  return floor($diff / (60 * 60 * 24));
}

// Helper function to get project status info
function getProjectStatusInfo($project)
{
  $status = $project->getStatus();
  $deadline = $project->getDeadline();

  if ($status === 'Done') {
    return [
      'class' => 'border-primary',
      'badge' => 'COMPLETED',
      'badgeClass' => 'bg-primary',
      'textClass' => 'text-primary'
    ];
  }

  $daysRemaining = getDaysRemaining($deadline);

  if ($daysRemaining === null) {
    return [
      'class' => 'border-secondary',
      'badge' => 'ACTIVE',
      'badgeClass' => 'bg-secondary',
      'textClass' => 'text-secondary',
      'daysText' => 'No deadline set'
    ];
  }

  if ($daysRemaining < 0) {
    return [
      'class' => 'border-danger',
      'badge' => 'OVERDUE',
      'badgeClass' => 'bg-danger',
      'textClass' => 'text-danger',
      'daysText' => abs($daysRemaining) . ' days overdue'
    ];
  }

  if ($daysRemaining <= 7) {
    return [
      'class' => 'border-warning',
      'badge' => 'DUE SOON',
      'badgeClass' => 'bg-warning text-dark',
      'textClass' => 'text-warning',
      'daysText' => $daysRemaining . ' days left'
    ];
  }

  return [
    'class' => 'border-success',
    'badge' => 'ON TRACK',
    'badgeClass' => 'bg-success',
    'textClass' => 'text-success',
    'daysText' => $daysRemaining . ' days left'
  ];
}
?>

<?php
// Determine the user's full title
$userTitle = $_SESSION['user_level'] ?? 'User';
if (($_SESSION['user_level'] ?? '') === 'Senior' && ($_SESSION['is_team_lead'] ?? false)) {
  $userTitle = 'Team Lead Senior';
}
?>

<!-- Welcome -->
<div class="user-welcome-card shadow">
  <div class="card-body-custom">
    <div class="d-flex align-items-center">
      <!-- <i class="fas fa-user-circle fa-3x me-4 text-white"></i> -->
      <i class="fa-solid fa-user-astronaut fa-3x me-4 text-white"></i>
      <div>
        <h3 class="mb-1">Hello, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>!</h3>
        <p class="mb-0 text-white-90">
          <?= htmlspecialchars($userTitle) ?> • <?= htmlspecialchars($_SESSION['user_email'] ?? 'N/A') ?>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Admin button -->
<?php if (isAdmin()): ?>
  <hr class="w-50 mx-auto">
  <div class="d-flex justify-content-center my-4">
    <a href="<?= Config::getBaseUrl() ?>index.php?page=admin_panel"
      class="btn btn-secondary border-2 border-warning shadow w-50">
      Go to Admin Panel
    </a>
  </div>
  <hr class="w-50 mx-auto">
<?php endif; ?>

<h3>Your Projects</h3>

<?php if (empty($projects)): ?>
  <p class="alert alert-info">You are not part of any projects yet.</p>
<?php else: ?>
  <div class="row">
    <?php foreach ($projects as $project): ?>
      <?php $statusInfo = getProjectStatusInfo($project); ?>
      <div class="col-lg-3 col-md-4 mb-4">
        <div class="card h-100 <?= $statusInfo['class'] ?>" style="border-width: 2px;">
          <div class="card-body d-flex flex-column shadow">
            <h5 class="card-title">
              <?= htmlspecialchars($project->getTitle()) ?>
              <hr>
              <span class="badge <?= $statusInfo['badgeClass'] ?> ms-2"><?= $statusInfo['badge'] ?></span>
            </h5>
            <p class="card-text flex-grow-1">
              <strong>Status:</strong> <?= htmlspecialchars($project->getStatus()) ?><br>
              <strong>Deadline:</strong>
              <span class="<?= $statusInfo['textClass'] ?> fw-bold">
                <?= htmlspecialchars($project->getDeadline() ? date('Y-m-d', strtotime($project->getDeadline())) : 'N/A') ?>
              </span>
              <br>
              <?php if (isset($statusInfo['daysText'])): ?>
                <small class="<?= $statusInfo['textClass'] ?>">
                  <strong><?= $statusInfo['daysText'] ?></strong>
                </small><br>
              <?php endif; ?>
              <strong>Role:</strong>
              <?php if (isAdmin()): ?>
                Admin
              <?php elseif ($project->getTeamLeadId() == $userId): ?>
                Team Lead
              <?php else: ?>
                Member
              <?php endif; ?>
            </p>
            <hr class="mt-auto">
            <a href="<?= Config::getBaseUrl() ?>index.php?page=project_view&id=<?= $project->getId() ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i> View</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <hr>
<?php endif; ?>