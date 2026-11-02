<?php
$extraScripts = $extraScripts ?? [];
?>
    </div><!-- /.app-main -->
  </div><!-- /.app-wrapper -->

  <!-- Toast Container -->
  <div id="toastContainer" class="toast-container-custom"></div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/leadflow/assets/js/app.js"></script>
<?php foreach ($extraScripts as $script): ?>
  <script src="<?= htmlspecialchars($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>

