<?php
/**
 * Halaman Pendaftaran Akun Pemilih Baru
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Jika sudah login, redirect
require_guest();

$error = '';
$inputs = [
    'nama' => '',
    'email' => '',
    'no_hp' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    $inputs['nama'] = $nama;
    $inputs['email'] = $email;
    $inputs['no_hp'] = $no_hp;

    // 1. Validasi Keamanan CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Sesi keamanan tidak valid (Invalid CSRF). Silakan refresh halaman dan coba kembali.';
    } elseif (empty($nama) || empty($email) || empty($no_hp) || empty($password) || empty($konfirmasi_password)) {
        $error = 'Seluruh kolom formulir pendaftaran wajib diisi.';
    } elseif (strlen($nama) < 3 || strlen($nama) > 100) {
        $error = 'Nama lengkap harus terdiri dari 3 hingga 100 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (!preg_match('/^[0-9+\-\s]{9,20}$/', $no_hp)) {
        $error = 'Nomor HP tidak valid. Gunakan format angka yang benar (minimal 9 digit).';
    } elseif (strlen($password) < 8) {
        $error = 'Kata sandi minimal harus berjumlah 8 karakter.';
    } elseif ($password !== $konfirmasi_password) {
        $error = 'Konfirmasi kata sandi tidak cocok dengan kata sandi yang dimasukkan.';
    } else {
        if (!$pdo) {
            $error = 'Koneksi database bermasalah. Pastikan MySQL XAMPP telah aktif.';
        } else {
            try {
                // Periksa apakah email telah terdaftar
                $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmtCheck->execute([$email]);
                if ($stmtCheck->fetch()) {
                    $error = 'Alamat email ini sudah terdaftar dalam sistem. Silakan masuk menggunakan akun tersebut.';
                } else {
                    // Hash Password menggunakan BCRYPT
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);

                    // Insert ke tabel users
                    $stmtInsert = $pdo->prepare("INSERT INTO users (nama, email, no_hp, password, role, status, created_at) VALUES (?, ?, ?, ?, 'pemilih', 'aktif', NOW())");
                    $stmtInsert->execute([$nama, $email, $no_hp, $password_hash]);

                    $new_user_id = (int)$pdo->lastInsertId();

                    // Catat log registrasi
                    log_activity($pdo, $new_user_id, 'Pemilih baru mendaftarkan akun secara mandiri', 'Registrasi');

                    set_flash('success', 'Pendaftaran akun berhasil! Silakan masuk menggunakan email dan kata sandi Anda.');
                    redirect(BASE_URL . 'auth/login.php');
                }
            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                $error = 'Terjadi kesalahan sistem saat menyimpan data pendaftaran. Silakan coba kembali.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun Pemilih — Kakang Senduk Ponorogo</title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/auth.css">
</head>
<body class="auth-body">

<div class="auth-bg-ornament"></div>

<div class="auth-card" style="max-width: 580px;">
  <div class="auth-header">
    <a href="<?= BASE_URL ?>index.php" title="Kembali ke Beranda">
      <img src="<?= BASE_URL ?>assets/img/logo.svg" alt="Logo Kakang Senduk" class="auth-logo">
    </a>
    <h2 class="auth-title">Pendaftaran Akun Pemilih</h2>
    <p class="auth-subtitle mb-0">Satu Akun, Dua Suara Berharga untuk Duta Wisata Ponorogo</p>
  </div>

  <div class="auth-body-content">
    <a href="<?= BASE_URL ?>index.php" class="auth-back-link text-muted">
      <i class="bi bi-arrow-left"></i> Kembali ke Beranda
    </a>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div><?= escape($error) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
      <?= csrf_input() ?>

      <div class="mb-3">
        <label for="nama" class="form-label">Nama Lengkap Sesuai KTP / Kartu Pelajar</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;">
            <i class="bi bi-person"></i>
          </span>
          <input type="text" class="form-control border-start-0 ps-1" id="nama" name="nama" value="<?= escape($inputs['nama']) ?>" placeholder="Contoh: Bima Arya Nugroho" required autofocus style="border-radius: 0 12px 12px 0;">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label for="email" class="form-label">Alamat Email Aktif</label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;">
              <i class="bi bi-envelope"></i>
            </span>
            <input type="email" class="form-control border-start-0 ps-1" id="email" name="email" value="<?= escape($inputs['email']) ?>" placeholder="nama@email.com" required style="border-radius: 0 12px 12px 0;">
          </div>
        </div>
        <div class="col-md-6">
          <label for="no_hp" class="form-label">Nomor WhatsApp / HP</label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;">
              <i class="bi bi-whatsapp"></i>
            </span>
            <input type="tel" class="form-control border-start-0 ps-1" id="no_hp" name="no_hp" value="<?= escape($inputs['no_hp']) ?>" placeholder="081234567890" required style="border-radius: 0 12px 12px 0;">
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label for="password" class="form-label">Kata Sandi (Min. 8 Karakter)</label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;">
              <i class="bi bi-shield-lock"></i>
            </span>
            <input type="password" class="form-control border-start-0 ps-1" id="password" name="password" placeholder="Minimal 8 karakter" required style="border-radius: 0 12px 12px 0;">
          </div>
        </div>
        <div class="col-md-6">
          <label for="konfirmasi_password" class="form-label">Ulangi Kata Sandi</label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;">
              <i class="bi bi-shield-check"></i>
            </span>
            <input type="password" class="form-control border-start-0 ps-1" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi kata sandi" required style="border-radius: 0 12px 12px 0;">
          </div>
        </div>
      </div>

      <button type="submit" class="auth-btn-submit">
        <i class="bi bi-person-plus me-2"></i> Buat Akun Pemilih
      </button>

      <div class="auth-footer">
        Sudah memiliki akun? <a href="<?= BASE_URL ?>auth/login.php">Masuk Sekarang</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

