<?php
/**
 * Halaman Galat 404 Not Found
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

http_response_code(404);
$page_title = '404 — Halaman Tidak Ditemukan';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5 text-center my-auto">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="p-5 rounded-4 bg-white shadow-sm border">
        <div class="display-1 fw-bold text-gold mb-2 font-serif">404</div>
        <h2 class="font-serif text-navy mb-3">Halaman Tidak Ditemukan</h2>
        <p class="text-secondary mb-4">
          Halaman atau data kontestan yang Anda cari tidak tersedia, telah dipindahkan, atau tautan yang Anda tuju sudah tidak aktif.
        </p>
        <div class="d-flex justify-content-center gap-3">
          <a href="<?= BASE_URL ?>index.php" class="btn btn-navy">
            <i class="bi bi-house-door me-2"></i> Ke Beranda
          </a>
          <a href="<?= BASE_URL ?>pages/kontestan.php" class="btn btn-gold">
            <i class="bi bi-people me-2"></i> Jelajahi Kontestan
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

