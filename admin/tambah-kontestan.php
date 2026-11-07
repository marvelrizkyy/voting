<?php
/**
 * Formulir Tambah Kontestan Baru (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Tambah Kontestan Baru';
$breadcrumb_title = 'Registrasi Kontestan';
$admin_active = 'kontestan';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <?= display_flash() ?>

  <div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
      <h5 class="admin-card-title"><i class="bi bi-person-plus-fill text-gold"></i> Formulir Pendaftaran Kontestan Baru</h5>
      <a href="<?= BASE_URL ?>admin/kontestan.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>

    <div class="admin-card-body p-4">
      <form action="<?= BASE_URL ?>proses/tambah-kontestan.php" method="POST" enctype="multipart/form-data">
        <?= csrf_input() ?>

        <div class="row g-4 mb-4">
          <!-- Nomor Peserta -->
          <div class="col-md-3">
            <label for="nomor_peserta" class="form-label">Nomor Peserta <span class="text-danger">*</span></label>
            <input type="text" class="form-control font-mono" id="nomor_peserta" name="nomor_peserta" placeholder="Contoh: K-09 atau S-09" required>
            <small class="text-muted" style="font-size: 0.75rem;">Awali 'K-' untuk Kakang, 'S-' untuk Senduk</small>
          </div>

          <!-- Nama Lengkap -->
          <div class="col-md-6">
            <label for="nama" class="form-label">Nama Lengkap Kontestan <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap dengan gelar jika ada" required>
          </div>

          <!-- Kategori -->
          <div class="col-md-3">
            <label for="kategori" class="form-label">Kategori Pemilihan <span class="text-danger">*</span></label>
            <select class="form-select" id="kategori" name="kategori" required>
              <option value="">-- Pilih Kategori --</option>
              <option value="Kakang">Kakang Ponorogo</option>
              <option value="Senduk">Senduk Ponorogo</option>
            </select>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <!-- Tempat Lahir -->
          <div class="col-md-4">
            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" placeholder="Contoh: Ponorogo">
          </div>

          <!-- Tanggal Lahir -->
          <div class="col-md-4">
            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir">
          </div>

          <!-- Asal Instansi -->
          <div class="col-md-4">
            <label for="asal_instansi" class="form-label">Asal Sekolah / Instansi / Kecamatan <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="asal_instansi" name="asal_instansi" placeholder="Contoh: SMA Negeri 1 Ponorogo" required>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <!-- Upload Foto Profil Resmi -->
          <div class="col-md-6">
            <label for="foto" class="form-label">Foto Resmi Kontestan (Rasio 4:5 / Potret) <span class="text-danger">*</span></label>
            <div class="image-upload-preview-card mb-2">
              <i class="bi bi-cloud-arrow-up text-gold fs-1 d-block mb-1"></i>
              <span class="small fw-bold text-navy d-block">Pilih Berkas Foto Potret</span>
              <span class="text-muted" style="font-size: 0.75rem;">Format didukung: JPG, PNG, WEBP (Maksimal 2 MB)</span>
              <input type="file" class="form-control mt-3" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" required>
            </div>
          </div>

          <!-- Status -->
          <div class="col-md-6">
            <label for="status" class="form-label">Status Partisipasi <span class="text-danger">*</span></label>
            <select class="form-select mb-3" id="status" name="status" required>
              <option value="aktif" selected>Aktif (Tampil di bilik suara publik)</option>
              <option value="nonaktif">Nonaktif / Draft</option>
            </select>

            <label for="deskripsi" class="form-label">Profil / Deskripsi Singkat</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Tuliskan latar belakang singkat dan ketertarikan kontestan pada pariwisata Ponorogo"></textarea>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <!-- Visi -->
          <div class="col-md-6">
            <label for="visi" class="form-label">Visi Duta Wisata</label>
            <textarea class="form-control" id="visi" name="visi" rows="3" placeholder="Tuliskan visi utama kontestan"></textarea>
          </div>

          <!-- Misi -->
          <div class="col-md-6">
            <label for="misi" class="form-label">Misi Duta Wisata (Poin-poin)</label>
            <textarea class="form-control" id="misi" name="misi" rows="3" placeholder="1. ...&#10;2. ...&#10;3. ..."></textarea>
          </div>
        </div>

        <!-- Prestasi -->
        <div class="mb-4">
          <label for="prestasi" class="form-label">Rekam Jejak & Prestasi yang Pernah Diraih</label>
          <textarea class="form-control" id="prestasi" name="prestasi" rows="3" placeholder="Daftar kejuaraan, organisasi seni, atau kontribusi budaya yang relevan"></textarea>
        </div>

        <div class="pt-3 border-top d-flex justify-content-end gap-2">
          <a href="<?= BASE_URL ?>admin/kontestan.php" class="btn btn-outline-secondary px-4 rounded-pill">Batal</a>
          <button type="submit" class="btn btn-gold-admin px-4 shadow-sm">
            <i class="bi bi-save me-1"></i> Simpan Data Kontestan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
