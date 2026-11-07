<?php
/**
 * Public Navbar Component
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */
require_once __DIR__ . '/functions.php';

$active_periode = get_active_periode($pdo ?? null);
$user_vote_status = null;
if (is_pemilih()) {
    $user_vote_status = get_user_vote_status($pdo ?? null, $_SESSION['user_id'], $active_periode['id'] ?? null);
}
?>
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>index.php">
      <img src="<?= BASE_URL ?>assets/img/logo.svg" alt="Logo Kakang Senduk Ponorogo">
    </a>

    <button class="navbar-toggler text-white border-0" type="button" data-bs-override="collapse" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list fs-2 text-gold"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= ($active_nav === 'beranda') ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">
            <i class="bi bi-house-door me-1"></i> Beranda
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($active_nav === 'kontestan') ? 'active' : '' ?>" href="<?= BASE_URL ?>pages/kontestan.php">
            <i class="bi bi-people me-1"></i> Kontestan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($active_nav === 'klasemen') ? 'active' : '' ?>" href="<?= BASE_URL ?>pages/klasemen.php">
            <i class="bi bi-trophy me-1"></i> Klasemen
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($active_nav === 'tentang') ? 'active' : '' ?>" href="<?= BASE_URL ?>pages/tentang.php">
            <i class="bi bi-info-circle me-1"></i> Tentang
          </a>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-3">
        <?php if (!is_logged_in()): ?>
          <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-gold btn-sm px-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
          </a>
          <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-gold btn-sm px-4">
            <i class="bi bi-check2-circle me-1"></i> Voting Sekarang
          </a>
        <?php elseif (is_admin()): ?>
          <a href="<?= BASE_URL ?>admin/dashboard.php" class="btn btn-gold btn-sm px-3">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard Admin
          </a>
          <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-outline-light btn-sm" title="Keluar">
            <i class="bi bi-box-arrow-right"></i>
          </a>
        <?php else: ?>
          <!-- Pemilih Terdaftar -->
          <div class="dropdown">
            <button class="btn btn-navy btn-sm dropdown-toggle d-flex align-items-center gap-2 border-gold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-check text-gold fs-5"></i>
              <span class="text-truncate" style="max-width: 140px;"><?= escape($_SESSION['user_nama'] ?? 'Pemilih') ?></span>
              <?php if ($user_vote_status): ?>
                <?php if ($user_vote_status['total_suara'] === 2): ?>
                  <span class="badge bg-success" title="2 Kategori Telah Dipilih"><i class="bi bi-check-all"></i> Selesai</span>
                <?php elseif ($user_vote_status['total_suara'] === 1): ?>
                  <span class="badge bg-warning text-dark" title="1 Kategori Belum Dipilih">1/2 Suara</span>
                <?php else: ?>
                  <span class="badge bg-secondary" title="Belum Memilih">0/2 Suara</span>
                <?php endif; ?>
              <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
              <li class="px-3 py-2 border-bottom">
                <small class="text-muted d-block">Masuk sebagai pemilih:</small>
                <strong><?= escape($_SESSION['user_email'] ?? '') ?></strong>
              </li>
              <li>
                <a class="dropdown-item py-2" href="<?= BASE_URL ?>pages/voting.php">
                  <i class="bi bi-box-seam me-2 text-gold"></i> Bilik Voting
                </a>
              </li>
              <li>
                <a class="dropdown-item py-2" href="<?= BASE_URL ?>pages/klasemen.php">
                  <i class="bi bi-graph-up me-2 text-primary"></i> Pantau Klasemen
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>auth/logout.php">
                  <i class="bi bi-box-arrow-right me-2"></i> Keluar
                </a>
              </li>
            </ul>
          </div>
          <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-gold btn-sm px-3 d-none d-md-inline-block">
            <i class="bi bi-check2-circle me-1"></i> Bilik Suara
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

