<?php
/**
 * Administrator Sidebar Navigation Component (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */
$admin_active = $admin_active ?? 'dashboard';

// Ambil jumlah ringkas untuk badge di sidebar
$sb_kontestan_count = 0;
$sb_pemilih_count = 0;
if (isset($pdo) && $pdo) {
    try {
        $sb_kontestan_count = (int)$pdo->query("SELECT COUNT(*) FROM kontestan")->fetchColumn();
        $sb_pemilih_count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pemilih'")->fetchColumn();
    } catch (Exception $e) {}
}
?>
<aside class="admin-sidebar" id="adminSidebar">
  <!-- Brand Header -->
  <div class="admin-sidebar-brand">
    <a href="<?= BASE_URL ?>admin/dashboard.php" class="admin-brand-link">
      <div class="admin-brand-icon-box">
        <img src="<?= BASE_URL ?>assets/img/emblem.svg" alt="Emblem Ponorogo" class="admin-brand-img">
      </div>
      <div class="admin-brand-text">
        <div class="admin-brand-title">Kakang Senduk</div>
        <div class="admin-brand-subtitle">Bumi Reog Ponorogo</div>
        <div class="admin-brand-badge">
          <span class="brand-live-dot"></span>
          <span>Command Center</span>
        </div>
      </div>
    </a>
  </div>

  <!-- Navigation Menu -->
  <div class="admin-sidebar-nav">
    <div class="admin-nav-section-title">
      <i class="bi bi-compass-fill"></i>
      <span>Ringkasan Utama</span>
    </div>
    <a href="<?= BASE_URL ?>admin/dashboard.php" class="admin-nav-link <?= ($admin_active === 'dashboard') ? 'active' : '' ?>">
      <div class="admin-nav-link-content">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Dashboard</span>
      </div>
      <?php if ($admin_active === 'dashboard'): ?>
        <span class="active-indicator-dot"></span>
      <?php endif; ?>
    </a>

    <div class="admin-nav-section-title">
      <i class="bi bi-stars"></i>
      <span>Master Pageant</span>
    </div>
    <a href="<?= BASE_URL ?>admin/kontestan.php" class="admin-nav-link <?= ($admin_active === 'kontestan') ? 'active' : '' ?>">
      <div class="admin-nav-link-content">
        <i class="bi bi-people-fill"></i>
        <span>Data Kontestan</span>
      </div>
      <?php if ($sb_kontestan_count > 0): ?>
        <span class="nav-count-badge"><?= $sb_kontestan_count ?></span>
      <?php elseif ($admin_active === 'kontestan'): ?>
        <span class="active-indicator-dot"></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>admin/pemilih.php" class="admin-nav-link <?= ($admin_active === 'pemilih') ? 'active' : '' ?>">
      <div class="admin-nav-link-content">
        <i class="bi bi-person-check-fill"></i>
        <span>Data Pemilih</span>
      </div>
      <?php if ($sb_pemilih_count > 0): ?>
        <span class="nav-count-badge"><?= $sb_pemilih_count ?></span>
      <?php elseif ($admin_active === 'pemilih'): ?>
        <span class="active-indicator-dot"></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>admin/periode.php" class="admin-nav-link <?= ($admin_active === 'periode') ? 'active' : '' ?>">
      <div class="admin-nav-link-content">
        <i class="bi bi-calendar2-range-fill"></i>
        <span>Periode Voting</span>
      </div>
      <?php if ($admin_active === 'periode'): ?>
        <span class="active-indicator-dot"></span>
      <?php endif; ?>
    </a>

    <div class="admin-nav-section-title">
      <i class="bi bi-graph-up-arrow"></i>
      <span>Rekapitulasi Suara</span>
    </div>
    <a href="<?= BASE_URL ?>admin/hasil-voting.php" class="admin-nav-link <?= ($admin_active === 'hasil') ? 'active' : '' ?>">
      <div class="admin-nav-link-content">
        <i class="bi bi-bar-chart-fill"></i>
        <span>Hasil Voting</span>
      </div>
      <?php if ($admin_active === 'hasil'): ?>
        <span class="active-indicator-dot"></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>admin/laporan.php" class="admin-nav-link <?= ($admin_active === 'laporan') ? 'active' : '' ?>">
      <div class="admin-nav-link-content">
        <i class="bi bi-file-earmark-pdf-fill"></i>
        <span>Laporan & Ekspor</span>
      </div>
      <?php if ($admin_active === 'laporan'): ?>
        <span class="active-indicator-dot"></span>
      <?php endif; ?>
    </a>

    <div class="admin-nav-section-title">
      <i class="bi bi-shield-lock-fill"></i>
      <span>Sistem & Keamanan</span>
    </div>
    <a href="<?= BASE_URL ?>admin/pengaturan.php" class="admin-nav-link <?= ($admin_active === 'pengaturan') ? 'active' : '' ?>">
      <div class="admin-nav-link-content">
        <i class="bi bi-gear-fill"></i>
        <span>Pengaturan Akun</span>
      </div>
      <?php if ($admin_active === 'pengaturan'): ?>
        <span class="active-indicator-dot"></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_URL ?>admin/logs.php" class="admin-nav-link <?= ($admin_active === 'logs') ? 'active' : '' ?>">
      <div class="admin-nav-link-content">
        <i class="bi bi-shield-shaded"></i>
        <span>Audit Trail Logs</span>
      </div>
      <?php if ($admin_active === 'logs'): ?>
        <span class="active-indicator-dot"></span>
      <?php endif; ?>
    </a>

    <div class="admin-nav-section-title">
      <i class="bi bi-globe"></i>
      <span>Tautan Cepat</span>
    </div>
    <a href="<?= BASE_URL ?>index.php" target="_blank" class="admin-nav-link public-portal-link">
      <div class="admin-nav-link-content">
        <i class="bi bi-box-arrow-up-right"></i>
        <span>Buka Web Publik</span>
      </div>
      <i class="bi bi-chevron-right text-muted opacity-50" style="font-size: 0.75rem;"></i>
    </a>
  </div>

  <!-- Bottom Profile Box -->
  <div class="admin-sidebar-footer">
    <div class="admin-sidebar-profile-box">
      <div class="admin-sidebar-avatar">
        <i class="bi bi-shield-fill"></i>
      </div>
      <div class="flex-grow-1 min-w-0">
        <div class="admin-profile-name text-truncate"><?= escape($_SESSION['user_nama'] ?? 'Admin Ponorogo') ?></div>
        <div class="admin-profile-role text-truncate">Dinas Pariwisata & Kebudayaan</div>
      </div>
      <a href="<?= BASE_URL ?>auth/logout.php" class="admin-logout-btn" title="Keluar dari Sistem">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>
  </div>
</aside>
