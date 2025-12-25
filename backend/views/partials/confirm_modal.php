<!-- Reusable Dynamic Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" id="confirmModalHeader">
        <h5 class="modal-title" id="confirmModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>Confirm Action
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="confirmModalMessage">
          Are you sure you want to perform this action?
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn" id="confirmModalBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>