<?php
/**
 * Halaman Galat 403 Forbidden
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

http_response_code(403);
$page_title = '403 — Akses Ditolak';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5 text-center my-auto">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="p-5 rounded-4 bg-white shadow-sm border">
        <div class="display-1 fw-bold text-danger mb-2 font-serif">403</div>
        <h2 class="font-serif text-navy mb-3">Akses Ditolak (Forbidden)</h2>
        <p class="text-secondary mb-4">
          Mohon maaf, Anda tidak memiliki izin atau hak akses yang memadai untuk membuka halaman ini. Area ini khusus diperuntukkan bagi Administrator sistem.
        </p>
        <div class="d-flex justify-content-center gap-3">
          <a href="<?= BASE_URL ?>index.php" class="btn btn-navy">
            <i class="bi bi-house-door me-2"></i> Kembali ke Beranda
          </a>
          <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-gold">
            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Akun Lain
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

