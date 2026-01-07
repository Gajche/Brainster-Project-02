<!-- Floating Buttons (Desktop Only) -->
<?php if (isLoggedIn()): ?>
  <div class="d-none d-lg-flex" style="position:fixed;bottom:20px;left:20px;z-index:9999;flex-direction:column;gap:10px">
    <button id="backBtn" class="btn btn-secondary rounded-circle shadow" style="width:50px;height:50px;transition:all 0.3s" onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'" title="Go back">
      <i class="fas fa-arrow-left"></i>
    </button>
  </div>

  <div class="d-none d-lg-flex" style="position:fixed; bottom:20px; right:20px; z-index:9999; flex-direction:column; gap:10px">
    <button id="scrollToTopBtn" class="btn btn-primary rounded-circle shadow" style="width:50px;height:50px;opacity:0;visibility:hidden;transition:all 0.3s" onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'" title="Scroll to top">
      <i class="fas fa-chevron-up"></i>
    </button>
  </div>
<?php endif; ?>

<!-- Footer -->
<footer class="text-center py-3 mt-5">
  <a href="https://cv-nikolovskidejan.vercel.app/" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
    <p><strong>&copy; <?= date('Y') ?> Project Management Application. All rights reserved &#128073; Dejan Nikolovski.</strong></p>
  </a>
</footer>