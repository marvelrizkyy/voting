<?php
/**
 * Pengaturan Akun Administrator (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Pengaturan Akun';
$breadcrumb_title = 'Konfigurasi Akun Pengawas';
$admin_active = 'pengaturan';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$user_id = (int)$_SESSION['user_id'];
$admin_user = null;

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, nama, email, no_hp, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $admin_user = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in pengaturan.php: " . $e->getMessage());
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Sesi keamanan berakhir (Invalid CSRF). Silakan refresh halaman.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $konfirmasi_baru = $_POST['konfirmasi_baru'] ?? '';

        if (empty($nama) || empty($no_hp)) {
            $error = 'Nama dan nomor telepon tidak boleh kosong.';
        } else {
            try {
                $stmtUp = $pdo->prepare("UPDATE users SET nama = ?, no_hp = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$nama, $no_hp, $user_id]);
                $_SESSION['user_nama'] = $nama;

                if (!empty($password_baru)) {
                    if (empty($password_lama)) {
                        $error = 'Kata sandi saat ini wajib diisi untuk mengganti kata sandi baru.';
                    } elseif (strlen($password_baru) < 8) {
                        $error = 'Kata sandi baru minimal harus 8 karakter.';
                    } elseif ($password_baru !== $konfirmasi_baru) {
                        $error = 'Konfirmasi kata sandi baru tidak cocok.';
                    } else {
                        $stmtCheckP = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                        $stmtCheckP->execute([$user_id]);
                        $current_hash = $stmtCheckP->fetchColumn();

                        if (!password_verify($password_lama, $current_hash)) {
                            $error = 'Kata sandi saat ini salah.';
                        } else {
                            $new_hash = password_hash($password_baru, PASSWORD_BCRYPT);
                            $stmtUpP = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                            $stmtUpP->execute([$new_hash, $user_id]);

                            log_activity($pdo, $user_id, 'Memperbarui profil dan kata sandi akun Administrator', 'Pengaturan');
                            set_flash('success', 'Profil dan kata sandi akun berhasil diperbarui.');
                            redirect(BASE_URL . 'admin/pengaturan.php');
                        }
                    }
                } else {
                    log_activity($pdo, $user_id, 'Memperbarui informasi profil akun Administrator', 'Pengaturan');
                    set_flash('success', 'Informasi profil berhasil diperbarui.');
                    redirect(BASE_URL . 'admin/pengaturan.php');
                }
            } catch (PDOException $e) {
                error_log("Error in update pengaturan: " . $e->getMessage());
                $error = 'Terjadi kesalahan sistem saat memperbarui profil.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <?= display_flash() ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 rounded-4 shadow-sm" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
      <div><?= escape($error) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
  <?php endif; ?>

  <div class="row g-4 justify-content-center">
    <div class="col-lg-8">
      <div class="admin-card">
        <div class="admin-card-header">
          <h5 class="admin-card-title"><i class="bi bi-person-gear text-gold"></i> Profil Akun Pengawas Resmi</h5>
        </div>
        <div class="admin-card-body p-4">
          <form action="" method="POST" autocomplete="off">
            <?= csrf_input() ?>

            <div class="mb-3">
              <label for="email" class="form-label">Alamat Email (Login Utama)</label>
              <input type="email" class="form-control bg-light font-mono" id="email" value="<?= escape($admin_user['email'] ?? '') ?>" disabled>
              <small class="text-muted" style="font-size: 0.75rem;">Email administrator utama bersifat permanen untuk keperluan audit trail.</small>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="nama" class="form-label">Nama Lengkap Pengawas <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?= escape($admin_user['nama'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label for="no_hp" class="form-label">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?= escape($admin_user['no_hp'] ?? '') ?>" required>
              </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold text-navy mb-2 font-serif">
              <i class="bi bi-shield-lock-fill text-gold me-2"></i>Pergantian Kata Sandi Akun
            </h6>
            <p class="small text-muted mb-3">Kosongkan bagian ini jika Anda tidak ingin memperbarui kata sandi.</p>

            <div class="mb-3">
              <label for="password_lama" class="form-label">Kata Sandi Saat Ini</label>
              <input type="password" class="form-control" id="password_lama" name="password_lama" placeholder="Masukkan kata sandi lama">
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="password_baru" class="form-label">Kata Sandi Baru</label>
                <input type="password" class="form-control" id="password_baru" name="password_baru" placeholder="Minimal 8 karakter">
              </div>
              <div class="col-md-6">
                <label for="konfirmasi_baru" class="form-label">Ulangi Kata Sandi Baru</label>
                <input type="password" class="form-control" id="konfirmasi_baru" name="konfirmasi_baru" placeholder="Ulangi kata sandi baru">
              </div>
            </div>

            <div class="pt-3 border-top text-end">
              <button type="submit" class="btn btn-gold-admin px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Simpan Pembaruan Akun
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
