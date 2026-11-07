<?php
/**
 * Administrator Footer Component
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */
?>
  <footer class="mt-auto py-3 px-4 bg-white border-top text-muted small d-flex justify-content-between align-items-center">
    <div>
      &copy; <?= date('Y') ?> <strong>Dinas Pariwisata Kabupaten Ponorogo</strong>. Panel Pengawas Voting Kakang Senduk.
    </div>
    <div>
      <span class="badge bg-light text-secondary border">Versi 1.0.0 • Production Ready</span>
    </div>
  </footer>
</div> <!-- /admin-main -->

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<!-- Admin Dashboard JS -->
<script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>

<?php if (!empty($extra_js)): ?>
  <?= $extra_js ?>
<?php endif; ?>

</body>
</html>

