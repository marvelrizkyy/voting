<?php
/**
 * Formulir Ubah Data Kontestan (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Edit Data Kontestan';
$breadcrumb_title = 'Ubah Data Kontestan';
$admin_active = 'kontestan';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$kontestan = null;

if ($pdo && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM kontestan WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $kontestan = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in edit-kontestan.php: " . $e->getMessage());
    }
}

if (!$kontestan) {
    set_flash('danger', 'Data kontestan tidak ditemukan.');
    redirect(BASE_URL . 'admin/kontestan.php');
}

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <?= display_flash() ?>

  <div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
      <h5 class="admin-card-title">
        <i class="bi bi-pencil-square text-gold"></i> Ubah Data: <?= escape($kontestan['nomor_peserta']) ?> — <?= escape($kontestan['nama']) ?>
      </h5>
      <a href="<?= BASE_URL ?>admin/kontestan.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="admin-card-body p-4">
      <form action="<?= BASE_URL ?>proses/edit-kontestan.php" method="POST" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= $kontestan['id'] ?>">

        <div class="row g-4 mb-4">
          <!-- Nomor Peserta -->
          <div class="col-md-3">
            <label for="nomor_peserta" class="form-label">Nomor Peserta <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-mono" id="nomor_peserta" name="nomor_peserta" value="<?= escape($kontestan['nomor_peserta']) ?>" required>
          </div>

          <!-- Nama Lengkap -->
          <div class="col-md-6">
            <label for="nama" class="form-label">Nama Lengkap Kontestan <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nama" name="nama" value="<?= escape($kontestan['nama']) ?>" required>
          </div>

          <!-- Kategori -->
          <div class="col-md-3">
            <label for="kategori" class="form-label">Kategori Pemilihan <span class="text-danger">*</span></label>
            <select class="form-select" id="kategori" name="kategori" required>
              <option value="Kakang" <?= ($kontestan['kategori'] === 'Kakang') ? 'selected' : '' ?>>Kakang Ponorogo</option>
              <option value="Senduk" <?= ($kontestan['kategori'] === 'Senduk') ? 'selected' : '' ?>>Senduk Ponorogo</option>
            </select>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <!-- Tempat Lahir -->
          <div class="col-md-4">
            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?= escape($kontestan['tempat_lahir'] ?? '') ?>">
          </div>

          <!-- Tanggal Lahir -->
          <div class="col-md-4">
            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= escape($kontestan['tanggal_lahir'] ?? '') ?>">
          </div>

          <!-- Asal Instansi -->
          <div class="col-md-4">
            <label for="asal_instansi" class="form-label">Asal Sekolah / Instansi <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="asal_instansi" name="asal_instansi" value="<?= escape($kontestan['asal_instansi']) ?>" required>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <!-- Upload Foto Profil Baru -->
          <div class="col-md-6">
            <label for="foto" class="form-label">Ganti Foto Resmi (Kosongkan jika tetap)</label>
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border mb-2">
              <img src="<?= get_kontestan_photo_url($kontestan['foto'], $kontestan['kategori']) ?>" alt="Foto Sekarang" class="rounded-3 object-fit-cover border border-gold shadow-sm" style="width: 55px; height: 70px;">
              <div class="small">
                <span class="fw-bold text-navy d-block">Foto Resmi Terpasang</span>
                <code class="text-muted" style="font-size: 0.75rem;"><?= escape($kontestan['foto']) ?></code>
              </div>
            </div>
            <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Maksimal 2 MB (JPG, PNG, WEBP)</small>
          </div>

          <!-- Status -->
          <div class="col-md-6">
            <label for="status" class="form-label">Status Kontestan <span class="text-danger">*</span></label>
            <select class="form-select mb-3" id="status" name="status" required>
              <option value="aktif" <?= ($kontestan['status'] === 'aktif') ? 'selected' : '' ?>>Aktif (Tampil di bilik suara publik)</option>
              <option value="nonaktif" <?= ($kontestan['status'] === 'nonaktif') ? 'selected' : '' ?>>Nonaktif / Diarsipkan</option>
            </select>

            <label for="deskripsi" class="form-label">Profil / Deskripsi Singkat</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= escape($kontestan['deskripsi'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <!-- Visi -->
          <div class="col-md-6">
            <label for="visi" class="form-label">Visi Duta Wisata</label>
            <textarea class="form-control" id="visi" name="visi" rows="3"><?= escape($kontestan['visi'] ?? '') ?></textarea>
          </div>

          <!-- Misi -->
          <div class="col-md-6">
            <label for="misi" class="form-label">Misi Duta Wisata</label>
            <textarea class="form-control" id="misi" name="misi" rows="3"><?= escape($kontestan['misi'] ?? '') ?></textarea>
          </div>
        </div>

        <!-- Prestasi -->
        <div class="mb-4">
          <label for="prestasi" class="form-label">Rekam Jejak & Prestasi</label>
          <textarea class="form-control" id="prestasi" name="prestasi" rows="3"><?= escape($kontestan['prestasi'] ?? '') ?></textarea>
        </div>

        <div class="pt-3 border-top d-flex justify-content-end gap-2">
          <a href="<?= BASE_URL ?>admin/kontestan.php" class="btn btn-outline-secondary px-4 rounded-pill">Batal</a>
          <button type="submit" class="btn btn-gold-admin px-4 shadow-sm">
            <i class="bi bi-save me-1"></i> Simpan Perubahan Data
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
