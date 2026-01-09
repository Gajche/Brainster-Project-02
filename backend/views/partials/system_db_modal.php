<div class="modal fade" id="systemDbModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fas fa-database me-2"></i>System Setup Required
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p id="systemDbMessage">
          Database is missing or not accessible.
        </p>
        <p class="text-muted small mb-0">
          This will create the database and seed sample data (DEV only).
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="systemDbInitBtn">Initialize Database</button>
        <button type="button" class="btn btn-primary" onclick="location.reload()">Refresh Page</button>
      </div>
    </div>
  </div>
</div>