<?php
/**
 * Administrator Topbar Component (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth-check.php';

// Pastikan hanya admin yang mengakses
require_admin();

$page_title = isset($page_title) ? $page_title . ' — Command Center' : 'Panel Administrator — ' . APP_NAME;
$css_version = file_exists(__DIR__ . '/../assets/css/admin.css') ? filemtime(__DIR__ . '/../assets/css/admin.css') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= escape($page_title) ?></title>

  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/emblem.svg">

  <!-- Google Fonts: Inter, Playfair Display & Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,400&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Admin Custom Stylesheet with Cache Buster -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css?v=<?= $css_version ?>">
  <?php if (!empty($extra_css)): ?>
    <?= $extra_css ?>
  <?php endif; ?>
</head>
<body class="admin-body">

<div class="admin-main">
  <!-- Executive Topbar -->
  <header class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-light d-lg-none p-2 rounded-8 border" type="button" id="sidebarToggle" aria-label="Toggle Sidebar">
        <i class="bi bi-list fs-4"></i>
      </button>
      <div class="admin-page-title-group">
        <h1 class="h5 mb-0 fw-bold"><?= escape($breadcrumb_title ?? 'Command Center') ?></h1>
        <span class="admin-page-subtitle">Sistem Pengawasan Pemilihan Kakang Senduk Ponorogo</span>
      </div>
    </div>

    <div class="d-flex align-items-center gap-3">
      <!-- Live Status Indicator -->
      <div class="live-status-pill d-none d-md-inline-flex">
        <span class="pulse-dot"></span>
        <span>DATABASE LIVE</span>
      </div>

      <!-- Quick Shortcut ke Web Publik -->
      <a href="<?= BASE_URL ?>index.php" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-none d-lg-inline-flex align-items-center gap-2 border-secondary border-opacity-25 shadow-sm">
        <i class="bi bi-box-arrow-up-right text-gold"></i>
        <span>Buka Web Publik</span>
      </a>

      <!-- Profile Dropdown -->
      <div class="dropdown">
        <button class="btn p-1 border-0 dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="admin-sidebar-avatar" style="width: 40px; height: 40px;">
            <i class="bi bi-shield-lock-fill"></i>
          </div>
          <div class="text-start d-none d-sm-block pe-1">
            <div class="fw-bold small text-navy leading-none"><?= escape($_SESSION['user_nama'] ?? 'Administrator') ?></div>
            <small class="badge bg-gold text-navy font-mono" style="font-size: 0.65rem;">SUPER ADMIN</small>
          </div>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2" style="min-width: 220px;">
          <li class="px-3 py-2 border-bottom mb-1">
            <small class="text-muted d-block" style="font-size: 0.7rem;">Masuk sebagai:</small>
            <strong class="text-navy text-truncate d-block small"><?= escape($_SESSION['user_email'] ?? '') ?></strong>
          </li>
          <li>
            <a class="dropdown-item py-2 rounded-3" href="<?= BASE_URL ?>admin/pengaturan.php">
              <i class="bi bi-gear me-2 text-gold"></i> Pengaturan Akun
            </a>
          </li>
          <li>
            <a class="dropdown-item py-2 rounded-3" href="<?= BASE_URL ?>admin/logs.php">
              <i class="bi bi-journal-text me-2 text-primary"></i> Audit Trail Sistem
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item py-2 text-danger rounded-3 fw-semibold" href="<?= BASE_URL ?>auth/logout.php">
              <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
            </a>
          </li>
        </ul>
      </div>
    </div>
  </header>
