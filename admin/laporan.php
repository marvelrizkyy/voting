<?php
/**
 * Modul Laporan & Ekspor Data (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Laporan & Ekspor Data';
$breadcrumb_title = 'Laporan Resmi Pemilihan';
$admin_active = 'laporan';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$all_periodes = [];
$selected_periode_id = isset($_GET['periode_id']) ? (int)$_GET['periode_id'] : 0;

if ($pdo) {
    try {
        $all_periodes = $pdo->query("SELECT id, nama_periode, status FROM periode_voting ORDER BY id DESC")->fetchAll();
        if ($selected_periode_id <= 0) {
            $active = get_active_periode($pdo);
            $selected_periode_id = $active ? (int)$active['id'] : (!empty($all_periodes) ? (int)$all_periodes[0]['id'] : 0);
        }
    } catch (PDOException $e) {
        error_log("Error in laporan.php: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <?= display_flash() ?>

  <div class="row g-4">
    <!-- Pilihan Format Ekspor -->
    <div class="col-lg-5">
      <div class="admin-card">
        <div class="admin-card-header">
          <h5 class="admin-card-title"><i class="bi bi-file-earmark-arrow-down-fill text-gold"></i> Ekspor Laporan Resmi</h5>
        </div>
        <div class="admin-card-body p-4">
          <form action="<?= BASE_URL ?>admin/laporan.php" method="GET" class="mb-4">
            <label for="periode_id" class="form-label fw-bold">Pilih Periode Pemilihan</label>
            <select name="periode_id" id="periode_id" class="form-select mb-3" onchange="this.form.submit()">
              <?php foreach ($all_periodes as $pr): ?>
                <option value="<?= $pr['id'] ?>" <?= ($pr['id'] == $selected_periode_id) ? 'selected' : '' ?>>
                  <?= escape($pr['nama_periode']) ?> <?= ($pr['status'] === 'aktif') ? '• [Sedang Berjalan]' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>

          <div class="d-grid gap-3">
            <!-- Ekspor PDF / Cetak Resmi -->
            <a href="<?= BASE_URL ?>proses/ekspor-pdf.php?periode_id=<?= $selected_periode_id ?>" target="_blank" class="p-3 bg-white text-navy border border-2 border-primary rounded-4 text-start d-flex align-items-center justify-content-between shadow-sm text-decoration-none transition-all hover-translate">
              <div>
                <div class="fw-bold text-navy fs-6 mb-1">
                  <i class="bi bi-file-earmark-pdf-fill text-danger me-2 fs-4"></i>Cetak / Ekspor Dokumen PDF Resmi
                </div>
                <small class="text-muted d-block">Format berita acara resmi bertanda tangan Kepala Dinas & Panitia</small>
              </div>
              <i class="bi bi-printer-fill fs-3 text-primary"></i>
            </a>

            <!-- Ekspor Excel -->
            <a href="<?= BASE_URL ?>proses/ekspor-excel.php?periode_id=<?= $selected_periode_id ?>" class="p-3 bg-white text-navy border border-2 border-success rounded-4 text-start d-flex align-items-center justify-content-between shadow-sm text-decoration-none transition-all hover-translate">
              <div>
                <div class="fw-bold text-navy fs-6 mb-1">
                  <i class="bi bi-file-earmark-spreadsheet-fill text-success me-2 fs-4"></i>Unduh Berkas Excel (.CSV / .XLS)
                </div>
                <small class="text-muted d-block">Lembar kerja tabel lengkap dengan UTF-8 BOM siap analisis</small>
              </div>
              <i class="bi bi-cloud-arrow-down-fill fs-3 text-success"></i>
            </a>
          </div>

          <div class="alert alert-light border mt-4 p-3 small text-secondary rounded-3">
            <i class="bi bi-shield-check text-gold fs-5 me-2 float-start"></i>
            <div>
              <strong>Integritas Dokumen Laporan:</strong> Dokumen memuat rincian Ranking, Kategori, Nomor Peserta, Nama Kontestan, Asal Instansi, Jumlah Suara Sah, dan Persentase perolehan resmi.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pratinjau Dokumen -->
    <div class="col-lg-7">
      <div class="admin-card">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
          <h5 class="admin-card-title"><i class="bi bi-eye text-gold"></i> Pratinjau Format Berita Acara</h5>
          <span class="badge badge-gold-pill">Dokumen Standar Panitia</span>
        </div>
        <div class="admin-card-body p-4 bg-light">
          <!-- Mockup Lembar Berita Acara -->
          <div class="bg-white p-4 rounded-4 shadow-sm border text-dark">
            <div class="text-center border-bottom pb-3 mb-3">
              <h6 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">PEMERINTAH KABUPATEN PONOROGO</h6>
              <h5 class="fw-bold mb-0 text-uppercase text-navy">DINAS KEBUDAYAAN, PARIWISATA, PEMUDA DAN OLAHRAGA</h5>
              <small class="text-muted font-mono" style="font-size: 0.72rem;">Jl. Pramuka No. 1 Ponorogo, Jawa Timur • Telp: (0352) 481234 • Email: pariwisata@ponorogo.go.id</small>
            </div>

            <div class="text-center mb-3">
              <strong class="d-block text-uppercase text-navy font-serif" style="text-decoration: underline;">BERITA ACARA REKAPITULASI HASIL VOTING</strong>
              <small class="text-muted">PEMILIHAN DUTA WISATA KAKANG SENDUK PONOROGO</small>
            </div>

            <p class="small text-secondary mb-3" style="text-align: justify;">
              Berdasarkan hasil pemungutan suara digital masyarakat yang telah ditutup dan diverifikasi oleh sistem, berikut adalah ringkasan perolehan suara sah:
            </p>

            <div class="p-3 bg-light rounded-3 text-center small text-muted font-mono mb-3 border">
              [Tabel Data Kontestan Kakang & Senduk, Nomor Peserta, Suara Sah, dan Persentase Tercetak Lengkap]
            </div>

            <div class="row text-center mt-4 pt-2" style="font-size: 0.78rem;">
              <div class="col-6">
                Mengetahui,<br>
                <strong>Kepala Dinas Pariwisata Ponorogo</strong><br><br><br><br>
                <strong class="text-decoration-underline">Dr. H. YUDI PRASETYO, M.Si.</strong><br>
                NIP. 19750812 200003 1 002
              </div>
              <div class="col-6">
                Ponorogo, <?= date('d F Y') ?><br>
                <strong>Ketua Panitia Pelaksana</strong><br><br><br><br>
                <strong class="text-decoration-underline">BAMBANG SURYONO, S.Sos.</strong><br>
                Ketua Panitia Pemilihan
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
