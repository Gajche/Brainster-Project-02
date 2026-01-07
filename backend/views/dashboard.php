<?php
extract($_SESSION['dashboard_data'] ?? []);
unset($_SESSION['dashboard_data']);
?>

<!-- Welcome -->
<div class="user-welcome-card shadow">
  <div class="card-body-custom">
    <div class="d-flex align-items-center">
      <i class="fa-solid fa-user-astronaut fa-3x me-4 text-white"></i>
      <div>
        <h3 class="mb-1">Hello, <?= htmlspecialchars($user_name) ?>!</h3>
        <p class="mb-0 text-white-90">
          <?= htmlspecialchars($user_title) ?> • <?= htmlspecialchars($user_email) ?>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Admin button -->
<?php if ($is_admin): ?>
  <hr class="w-50 mx-auto">
  <div class="d-flex justify-content-center my-4">
    <a href="<?= Config::getBaseUrl() ?>index.php?page=admin_panel" class="btn btn-secondary border-2 border-warning shadow w-50">
      <i class="fas fa-shield-halved me-2"></i> Go to Admin Panel
    </a>
  </div>
  <hr class="w-50 mx-auto">
<?php endif; ?>

<!-- <h3>Your Projects</h3> -->
<h3>
  <i class="fas fa-code-branch me-2"></i>Your Projects
</h3>
<!-- Helper to get project status info -->
<?php if (empty($projects)): ?>
  <p class="alert alert-info">You are not part of any projects yet.</p>
<?php else: ?>
  <div class="row">
    <?php foreach ($projects as $project): ?>
      <?php $statusInfo = ProjectHelper::getStatusInfo($project); // Compute here 
      ?>
      <div class="col-lg-3 col-md-4 mb-4">
        <div class="card h-100 <?= $statusInfo['class'] ?>" style="border-width: 2px;">
          <div class="card-body d-flex flex-column shadow">
            <h5 class="card-title">
              <?= htmlspecialchars($project->getTitle()) ?>
              <hr>
              <span class="badge <?= $statusInfo['badgeClass'] ?>"><?= $statusInfo['badge'] ?></span>
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
              <?php if ($is_admin): ?>
                Admin
              <?php elseif ($project->getTeamLeadId() == $user_id): ?>
                Team Lead
              <?php else: ?>
                Member
              <?php endif; ?>
            </p>
            <hr class="mt-auto">
            <a href="<?= Config::getBaseUrl() ?>index.php?page=project_view&id=<?= $project->getId() ?>" class="btn btn-sm btn-primary">
              <i class="fas fa-eye me-1"></i> View
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <hr>
<?php endif; ?>