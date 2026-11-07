<?php
/**
 * Public Footer Component
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */
?>
<footer class="main-footer mt-auto">
  <div class="container">
    <div class="row g-4 justify-content-between">
      <div class="col-lg-5 col-md-6">
        <img src="<?= BASE_URL ?>assets/img/logo.svg" alt="Logo Kakang Senduk" class="footer-logo mb-3">
        <p class="text-secondary small pe-lg-4">
          Aplikasi Pemilihan Duta Wisata Daerah Kabupaten Ponorogo berbasis web. Wadah apresiasi masyarakat Bumi Reog dalam memilih representasi pemuda-pemudi inspiratif, berkarakter luhur, dan berdaya saing global untuk memajukan pariwisata Ponorogo.
        </p>
        <div class="d-flex gap-3 text-gold fs-5 mt-3">
          <span title="Bumi Reog"><i class="bi bi-shield-shaded"></i></span>
          <span title="Duta Wisata Jawa Timur"><i class="bi bi-award"></i></span>
          <span title="Ekowisata & Budaya"><i class="bi bi-tree"></i></span>
          <span title="Transparan & Akuntabel"><i class="bi bi-patch-check"></i></span>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="text-white font-serif mb-3 text-gold">Navigasi Utama</h6>
        <ul class="list-unstyled">
          <li><a href="<?= BASE_URL ?>index.php" class="footer-link"><i class="bi bi-chevron-right small text-gold me-1"></i> Beranda</a></li>
          <li><a href="<?= BASE_URL ?>pages/kontestan.php" class="footer-link"><i class="bi bi-chevron-right small text-gold me-1"></i> Daftar Kontestan</a></li>
          <li><a href="<?= BASE_URL ?>pages/voting.php" class="footer-link"><i class="bi bi-chevron-right small text-gold me-1"></i> Bilik Voting</a></li>
          <li><a href="<?= BASE_URL ?>pages/klasemen.php" class="footer-link"><i class="bi bi-chevron-right small text-gold me-1"></i> Hasil Klasemen</a></li>
          <li><a href="<?= BASE_URL ?>pages/tentang.php" class="footer-link"><i class="bi bi-chevron-right small text-gold me-1"></i> Tentang Kakang Senduk</a></li>
        </ul>
      </div>

      <div class="col-lg-4 col-md-12">
        <h6 class="text-white font-serif mb-3 text-gold">Informasi Sekretariat</h6>
        <p class="small text-secondary mb-2">
          <i class="bi bi-geo-alt text-gold me-2"></i> Dinas Kebudayaan, Pariwisata, Pemuda dan Olahraga Kabupaten Ponorogo
        </p>
        <p class="small text-secondary mb-2">
          <i class="bi bi-envelope text-gold me-2"></i> pariwisata@ponorogo.go.id
        </p>
        <p class="small text-secondary mb-3">
          <i class="bi bi-telephone text-gold me-2"></i> (0352) 481234 • Ponorogo, Jawa Timur
        </p>
        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(214, 174, 88, 0.2);">
          <small class="text-gold d-block font-serif mb-1">Integritas Pemilihan</small>
          <small class="text-secondary" style="font-size: 0.78rem;">Satu akun pemilih berhak memberikan tepat satu suara untuk Kakang dan satu suara untuk Senduk. Hasil voting dihitung secara otomatis dan sah.</small>
        </div>
      </div>
    </div>

    <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary">
      <div>
        &copy; <?= date('Y') ?> Pemilihan Kakang Senduk Kabupaten Ponorogo. Seluruh Hak Cipta Dilindungi.
      </div>
      <div class="mt-2 mt-md-0">
        <span class="badge" style="background: rgba(214, 174, 88, 0.15); color: #F2D07C; border: 1px solid rgba(214, 174, 88, 0.3);">
          Bumi Reog • Kota Kreatif Dunia
        </span>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<?php if (!empty($extra_js)): ?>
  <?= $extra_js ?>
<?php endif; ?>
</body>
</html>

