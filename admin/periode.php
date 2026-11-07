<?php
/**
 * Manajemen Periode Voting (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Manajemen Periode Voting';
$breadcrumb_title = 'Konfigurasi Periode Pemilihan';
$admin_active = 'periode';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$periode_list = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.*,
                   (SELECT COUNT(*) FROM voting v WHERE v.periode_voting_id = p.id) AS total_suara
            FROM periode_voting p
            ORDER BY p.id DESC
        ");
        $periode_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in admin periode.php: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <?= display_flash() ?>

  <div class="row g-4 mb-4">
    <!-- Formulir Tambah / Edit Periode -->
    <div class="col-lg-5">
      <div class="admin-card">
        <div class="admin-card-header">
          <h5 class="admin-card-title"><i class="bi bi-calendar-plus-fill text-gold"></i> Buat Periode Baru</h5>
        </div>
        <div class="admin-card-body p-4">
          <form action="<?= BASE_URL ?>proses/simpan-periode.php" method="POST">
            <?= csrf_input() ?>
            <input type="hidden" name="id" id="periodeId" value="0">

            <div class="mb-3">
              <label for="nama_periode" class="form-label">Nama Periode Pemilihan <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nama_periode" name="nama_periode" placeholder="Contoh: Pemilihan Kakang Senduk 2026" required>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-sm-6">
                <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control font-mono" id="tanggal_mulai" name="tanggal_mulai" required>
              </div>
              <div class="col-sm-6">
                <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control font-mono" id="tanggal_selesai" name="tanggal_selesai" required>
              </div>
            </div>

            <div class="mb-4">
              <label for="status" class="form-label">Status Awal</label>
              <select class="form-select" id="status" name="status" required>
                <option value="aktif">Aktif (Akan menonaktifkan periode aktif lain)</option>
                <option value="nonaktif" selected>Nonaktif (Draft)</option>
              </select>
              <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                <i class="bi bi-shield-check text-success me-1"></i>Sistem secara otomatis mencegah dua periode aktif bersamaan.
              </small>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
              <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnResetPeriode" style="display: none;">
                Batal Edit
              </button>
              <button type="submit" class="btn btn-gold-admin btn-sm px-4 ms-auto shadow-sm" id="btnSubmitPeriode">
                <i class="bi bi-save me-1"></i> Simpan Periode
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Riwayat & Daftar Periode -->
    <div class="col-lg-7">
      <div class="admin-card">
        <div class="admin-card-header">
          <h5 class="admin-card-title"><i class="bi bi-clock-history text-gold"></i> Riwayat Periode Pemilihan</h5>
        </div>
        <div class="admin-card-body p-0">
          <div class="table-responsive">
            <table class="table table-custom mb-0">
              <thead>
                <tr>
                  <th>Nama Periode</th>
                  <th>Jadwal Pelaksanaan</th>
                  <th>Suara Sah</th>
                  <th>Status</th>
                  <th class="text-end pe-4">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($periode_list)): ?>
                  <?php foreach ($periode_list as $p): ?>
                    <?php 
                      $now = date('Y-m-d H:i:s');
                      $is_expired = ($now > $p['tanggal_selesai']);
                      $not_started = ($now < $p['tanggal_mulai']);
                    ?>
                    <tr>
                      <td>
                        <span class="fw-bold text-navy d-block"><?= escape($p['nama_periode']) ?></span>
                        <small class="text-muted font-mono">ID: #<?= $p['id'] ?></small>
                      </td>
                      <td>
                        <div class="small text-dark font-mono"><i class="bi bi-play-circle-fill text-success me-1"></i><?= format_tanggal_indo($p['tanggal_mulai']) ?></div>
                        <div class="small text-muted font-mono"><i class="bi bi-stop-circle-fill text-danger me-1"></i><?= format_tanggal_indo($p['tanggal_selesai']) ?></div>
                        <?php if ($is_expired): ?>
                          <span class="badge bg-danger bg-opacity-10 text-danger mt-1 rounded-pill" style="font-size: 0.68rem;">Waktu Telah Habis</span>
                        <?php elseif ($not_started): ?>
                          <span class="badge bg-warning bg-opacity-25 text-dark mt-1 rounded-pill" style="font-size: 0.68rem;">Belum Dimulai</span>
                        <?php else: ?>
                          <span class="badge bg-success bg-opacity-10 text-success mt-1 rounded-pill" style="font-size: 0.68rem;">Sedang Berlangsung</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="fw-bold text-navy font-mono"><?= number_format($p['total_suara'], 0, ',', '.') ?></span>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">vote</small>
                      </td>
                      <td>
                        <?php if ($p['status'] === 'aktif'): ?>
                          <span class="badge bg-success px-2 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                        <?php else: ?>
                          <span class="badge bg-secondary px-2 py-1 rounded-pill">Nonaktif</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                          <form action="<?= BASE_URL ?>proses/toggle-periode.php" method="POST" class="d-inline">
                            <?= csrf_input() ?>
                            <input type="hidden" name="periode_id" value="<?= $p['id'] ?>">
                            <?php if ($p['status'] === 'aktif'): ?>
                              <button type="submit" name="action" value="deactivate" class="btn btn-outline-warning" title="Tutup / Nonaktifkan Periode" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan periode ini? Voting akan terhenti.');">
                                <i class="bi bi-pause-fill"></i> Tutup
                              </button>
                            <?php else: ?>
                              <button type="submit" name="action" value="activate" class="btn btn-outline-success" title="Aktifkan Periode Ini" onclick="return confirm('Aktifkan periode ini? Periode lain yang aktif akan otomatis dinonaktifkan.');">
                                <i class="bi bi-play-fill"></i> Buka
                              </button>
                            <?php endif; ?>
                          </form>

                          <button type="button" class="btn btn-outline-primary btn-edit-periode ms-1"
                                  data-id="<?= $p['id'] ?>"
                                  data-nama="<?= escape($p['nama_periode']) ?>"
                                  data-mulai="<?= date('Y-m-d\TH:i', strtotime($p['tanggal_mulai'])) ?>"
                                  data-selesai="<?= date('Y-m-d\TH:i', strtotime($p['tanggal_selesai'])) ?>"
                                  data-status="<?= $p['status'] ?>"
                                  title="Edit Periode">
                            <i class="bi bi-pencil-square"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Belum ada periode voting yang dibuat.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const editBtns = document.querySelectorAll('.btn-edit-periode');
    const inputId = document.getElementById('periodeId');
    const inputNama = document.getElementById('nama_periode');
    const inputMulai = document.getElementById('tanggal_mulai');
    const inputSelesai = document.getElementById('tanggal_selesai');
    const inputStatus = document.getElementById('status');
    const btnReset = document.getElementById('btnResetPeriode');
    const btnSubmit = document.getElementById('btnSubmitPeriode');

    editBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        inputId.value = this.getAttribute('data-id');
        inputNama.value = this.getAttribute('data-nama');
        inputMulai.value = this.getAttribute('data-mulai');
        inputSelesai.value = this.getAttribute('data-selesai');
        inputStatus.value = this.getAttribute('data-status');

        btnReset.style.display = 'inline-block';
        btnSubmit.innerHTML = '<i class="bi bi-pencil me-1"></i> Update Periode';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    });

    if (btnReset) {
      btnReset.addEventListener('click', function () {
        inputId.value = '0';
        inputNama.value = '';
        inputMulai.value = '';
        inputSelesai.value = '';
        inputStatus.value = 'nonaktif';
        btnReset.style.display = 'none';
        btnSubmit.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Periode';
      });
    }
  });
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
