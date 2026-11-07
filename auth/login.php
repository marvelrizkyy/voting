<?php
/**
 * Halaman Login Pengguna (Admin & Pemilih)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Jika sudah login, redirect sesuai hak akses
require_guest();

$error = '';
$email_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    $email_input = $email;

    // 1. Validasi CSRF Token
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Sesi keamanan berakhir (Invalid CSRF). Silakan refresh halaman dan coba lagi.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        if (!$pdo) {
            $error = 'Gagal terhubung ke database. Pastikan layanan MySQL telah aktif.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, nama, email, password, role, status FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Periksa status akun
                    if ($user['status'] !== 'aktif') {
                        $error = 'Akun Anda dinonaktifkan oleh administrator. Hubungi panitia pelaksana.';
                    } else {
                        // Regenerasi Session ID untuk mitigasi Session Fixation
                        session_regenerate_id(true);

                        $_SESSION['user_id'] = (int)$user['id'];
                        $_SESSION['user_nama'] = $user['nama'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];

                        // Catat log login
                        log_activity($pdo, $user['id'], 'Pengguna berhasil masuk ke sistem (' . $user['role'] . ')', 'Autentikasi');

                        set_flash('success', 'Selamat datang kembali, ' . escape($user['nama']) . '!');

                        // Redirect sesuai peran (RBAC)
                        if ($user['role'] === 'admin') {
                            redirect(BASE_URL . 'admin/dashboard.php');
                        } else {
                            redirect(BASE_URL . 'pages/voting.php');
                        }
                    }
                } else {
                    $error = 'Kombinasi email atau kata sandi tidak sesuai.';
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'Terjadi kesalahan sistem saat memproses login. Silakan coba beberapa saat lagi.';
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
  <title>Masuk Akun — Kakang Senduk Ponorogo</title>
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

<div class="auth-card">
  <div class="auth-header">
    <a href="<?= BASE_URL ?>index.php" title="Kembali ke Beranda">
      <img src="<?= BASE_URL ?>assets/img/logo.svg" alt="Logo Kakang Senduk" class="auth-logo">
    </a>
    <h2 class="auth-title">Bilik Suara Digital</h2>
    <p class="auth-subtitle mb-0">Masuk untuk memberikan suara bagi kontestan pilihan Anda</p>
  </div>

  <div class="auth-body-content">
    <a href="<?= BASE_URL ?>index.php" class="auth-back-link text-muted">
      <i class="bi bi-arrow-left"></i> Kembali ke Beranda
    </a>

    <?= display_flash() ?>

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
        <label for="email" class="form-label">Alamat Email</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;">
            <i class="bi bi-envelope"></i>
          </span>
          <input type="email" class="form-control border-start-0 ps-1" id="email" name="email" value="<?= escape($email_input) ?>" placeholder="nama@email.com" required autofocus style="border-radius: 0 12px 12px 0;">
        </div>
      </div>

      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label for="password" class="form-label mb-0">Kata Sandi</label>
        </div>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;">
            <i class="bi bi-shield-lock"></i>
          </span>
          <input type="password" class="form-control border-start-0 border-end-0 ps-1" id="password" name="password" placeholder="Masukkan kata sandi" required>
          <button class="btn btn-light border border-start-0 text-muted" type="button" id="togglePassword" style="border-radius: 0 12px 12px 0;">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="auth-btn-submit">
        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
      </button>

      <div class="auth-footer">
        Belum memiliki akun pemilih? <a href="<?= BASE_URL ?>auth/register.php">Daftar Akun Baru</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Toggle Password Visibility
  const toggleBtn = document.getElementById('togglePassword');
  const pwdInput = document.getElementById('password');
  if (toggleBtn && pwdInput) {
    toggleBtn.addEventListener('click', function () {
      const type = pwdInput.getAttribute('type') === 'password' ? 'text' : 'password';
      pwdInput.setAttribute('type', type);
      this.querySelector('i').classList.toggle('bi-eye');
      this.querySelector('i').classList.toggle('bi-eye-slash');
    });
  }
</script>
</body>
</html>

