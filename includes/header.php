<?php
/**
 * Public Header Component
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/functions.php';

$page_title = isset($page_title) ? $page_title . ' — ' . APP_NAME : APP_NAME . ' — ' . APP_TITLE;
$active_nav = $active_nav ?? 'beranda';
$css_version = file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= escape($page_title) ?></title>
  <meta name="description" content="Platform Pemilihan Duta Wisata Daerah Kabupaten Ponorogo — Kakang Senduk. Dukung kontestan favoritmu secara transparan dan berintegritas.">
  <meta name="keywords" content="Kakang Senduk, Ponorogo, Duta Wisata, Pemilihan, Voting, Reyog Ponorogo">

  <!-- Favicon Emblem -->
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">

  <!-- Google Fonts: Playfair Display, Poppins, Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Custom Stylesheet with Cache Buster -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= $css_version ?>">
  <?php if (!empty($extra_css)): ?>
    <?= $extra_css ?>
  <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100">

