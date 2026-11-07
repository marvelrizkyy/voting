<?php
/**
 * Manajemen Data Kontestan (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Manajemen Kontestan';
$breadcrumb_title = 'Kelola Finalis Kontestan';
$admin_active = 'kontestan';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$filter_kat = trim($_GET['kat'] ?? 'all');
$filter_status = trim($_GET['status'] ?? 'all');
$search = trim($_GET['q'] ?? '');

$kontestan_list = [];
$count_kakang = 0;
$count_senduk = 0;
$total_suara_all = 0;

if ($pdo) {
    try {
        $count_kakang = (int)$pdo->query("SELECT COUNT(*) FROM kontestan WHERE kategori = 'Kakang'")->fetchColumn();
        $count_senduk = (int)$pdo->query("SELECT COUNT(*) FROM kontestan WHERE kategori = 'Senduk'")->fetchColumn();
        $total_suara_all = (int)$pdo->query("SELECT COUNT(*) FROM voting")->fetchColumn();

        $sql = "
            SELECT k.*, 
                   (SELECT COUNT(*) FROM voting v WHERE v.kontestan_id = k.id) AS jumlah_suara
            FROM kontestan k
            WHERE 1=1
        ";
        $params = [];

        if (in_array($filter_kat, ['Kakang', 'Senduk'])) {
            $sql .= " AND k.kategori = :kat";
            $params[':kat'] = $filter_kat;
        }

        if (in_array($filter_status, ['aktif', 'nonaktif'])) {
            $sql .= " AND k.status = :status";
            $params[':status'] = $filter_status;
        }

        if (!empty($search)) {
            $sql .= " AND (k.nama LIKE :kw OR k.nomor_peserta LIKE :kw OR k.asal_instansi LIKE :kw)";
            $params[':kw'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY k.kategori ASC, k.nomor_peserta ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $kontestan_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in admin kontestan.php: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <?= display_flash() ?>

  <!-- Summary KPI Bar -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center justify-content-between">
        <div>
          <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Finalis Kakang</small>
          <span class="fs-4 fw-bold text-navy"><?= $count_kakang ?> Peserta</span>
        </div>
        <div class="p-2 rounded-3 bg-navy text-gold fs-4 px-3">
          <i class="bi bi-gender-male"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center justify-content-between">
        <div>
          <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Finalis Senduk</small>
          <span class="fs-4 fw-bold" style="color: #A67C2E;"><?= $count_senduk ?> Peserta</span>
        </div>
        <div class="p-2 rounded-3 text-white fs-4 px-3" style="background: linear-gradient(135deg, #4A154B, #220B24);">
          <i class="bi bi-gender-female"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center justify-content-between">
        <div>
          <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Total Suara Terkumpul</small>
          <span class="fs-4 fw-bold text-success"><?= number_format($total_suara_all, 0, ',', '.') ?> Vote</span>
        </div>
        <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success fs-4 px-3">
          <i class="bi bi-check2-all"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <h5 class="admin-card-title"><i class="bi bi-people-fill text-gold"></i> Master Data Kontestan</h5>
        <small class="text-muted">Total: <?= count($kontestan_list) ?> peserta ditemukan</small>
      </div>
      <div>
        <a href="<?= BASE_URL ?>admin/tambah-kontestan.php" class="btn btn-gold-admin shadow-sm">
          <i class="bi bi-person-plus-fill me-1"></i> Tambah Kontestan Baru
        </a>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="p-3 bg-light border-bottom">
      <form action="" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
          <select name="kat" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all" <?= $filter_kat === 'all' ? 'selected' : '' ?>>Semua Kategori (Kakang & Senduk)</option>
            <option value="Kakang" <?= $filter_kat === 'Kakang' ? 'selected' : '' ?>>Hanya Kakang</option>
            <option value="Senduk" <?= $filter_kat === 'Senduk' ? 'selected' : '' ?>>Hanya Senduk</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Semua Status</option>
            <option value="aktif" <?= $filter_status === 'aktif' ? 'selected' : '' ?>>Hanya Aktif</option>
            <option value="nonaktif" <?= $filter_status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif / Diarsipkan</option>
          </select>
        </div>
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control border-start-0" placeholder="Cari nama, nomor, atau instansi..." value="<?= escape($search) ?>">
            <button class="btn btn-navy" type="submit">Cari</button>
          </div>
        </div>
        <div class="col-md-2 text-md-end">
          <a href="<?= BASE_URL ?>admin/kontestan.php" class="btn btn-outline-secondary btn-sm w-100">Reset Filter</a>
        </div>
      </form>
    </div>

    <div class="admin-card-body p-0">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th style="width: 85px;" class="text-center">No. Peserta</th>
              <th>Foto & Nama Lengkap</th>
              <th>Kategori</th>
              <th>Asal Sekolah / Instansi</th>
              <th>Perolehan Suara</th>
              <th>Status</th>
              <th class="text-end pe-4" style="width: 170px;">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($kontestan_list)): ?>
              <?php foreach ($kontestan_list as $k): ?>
                <tr>
                  <td class="text-center">
                    <span class="badge bg-navy text-gold font-mono fs-6 px-2 py-1"><?= escape($k['nomor_peserta']) ?></span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?= get_kontestan_photo_url($k['foto'], $k['kategori']) ?>" alt="<?= escape($k['nama']) ?>" class="rounded-3 object-fit-cover shadow-sm border border-gold" style="width: 48px; height: 60px;">
                      <div>
                        <div class="fw-bold text-navy fs-6"><?= escape($k['nama']) ?></div>
                        <small class="text-muted"><i class="bi bi-geo-alt me-1 text-gold"></i><?= escape($k['tempat_lahir'] ?? 'Ponorogo') ?></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge <?= $k['kategori'] === 'Kakang' ? 'badge-kakang-pill' : 'badge-senduk-pill' ?>">
                      <?= escape($k['kategori']) ?>
                    </span>
                  </td>
                  <td>
                    <span class="text-secondary small fw-medium"><?= escape($k['asal_instansi']) ?></span>
                  </td>
                  <td>
                    <span class="badge bg-light text-navy border font-mono fs-6 px-2 py-1">
                      <?= number_format($k['jumlah_suara'], 0, ',', '.') ?> Vote
                    </span>
                  </td>
                  <td>
                    <?php if ($k['status'] === 'aktif'): ?>
                      <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                      </span>
                    <?php else: ?>
                      <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 rounded-pill">
                        <i class="bi bi-dash-circle me-1"></i>Nonaktif
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm">
                      <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $k['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="Lihat Tampilan Publik">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="<?= BASE_URL ?>admin/edit-kontestan.php?id=<?= $k['id'] ?>" class="btn btn-outline-primary" title="Ubah Profil Kontestan">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <button type="button" class="btn btn-outline-danger btn-delete-kontestan" 
                              data-id="<?= $k['id'] ?>" 
                              data-nama="<?= escape($k['nama']) ?>"
                              data-suara="<?= $k['jumlah_suara'] ?>"
                              title="Hapus / Arsipkan">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-1 d-block mb-2 text-gold"></i>
                  Tidak ada data kontestan yang sesuai kriteria filter.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Hapus / Arsip Kontestan -->
<div class="modal fade" id="deleteKontestanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
      <div class="modal-header bg-danger text-white py-3">
        <h5 class="modal-title font-serif"><i class="bi bi-exclamation-octagon me-2"></i>Konfirmasi Tindakan Kontestan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <form action="<?= BASE_URL ?>proses/hapus-kontestan.php" method="POST">
        <?= csrf_input() ?>
        <input type="hidden" name="id" id="deleteId" value="">
        <div class="modal-body p-4 text-center">
          <p class="mb-2 text-muted">Tindakan terhadap kontestan:</p>
          <h5 id="deleteNama" class="fw-bold text-navy font-serif mb-3"></h5>
          
          <div id="deleteWarningSuara" class="alert alert-warning text-start small mb-0 rounded-3 border-0 bg-warning bg-opacity-10 text-dark">
            <i class="bi bi-shield-lock-fill text-warning fs-4 me-2 float-start"></i>
            <div>
              <strong>Proteksi Integritas Data Voting:</strong> Kontestan ini telah memiliki suara sah. Untuk menjaga keabsahan rekapitulasi, data <strong>tidak akan dihapus permanen</strong> melainkan akan <strong>dinonaktifkan / diarsipkan</strong>.
            </div>
          </div>

          <div id="deleteWarningBebas" class="alert alert-danger text-start small mb-0 rounded-3 border-0 bg-danger bg-opacity-10 text-dark" style="display: none;">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-2 float-start"></i>
            <div>
              Kontestan ini belum memiliki suara sah. Data dan berkas foto akan dihapus permanen dari sistem.
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger px-4" id="btnConfirmDelete">
            Lanjutkan Tindakan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.btn-delete-kontestan');
    const deleteModalEl = document.getElementById('deleteKontestanModal');
    let deleteModal = null;
    if (deleteModalEl && typeof bootstrap !== 'undefined') {
      deleteModal = new bootstrap.Modal(deleteModalEl);
    }

    deleteButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const nama = this.getAttribute('data-nama');
        const suara = parseInt(this.getAttribute('data-suara') || '0', 10);

        document.getElementById('deleteId').value = id;
        document.getElementById('deleteNama').textContent = nama;

        const warnSuara = document.getElementById('deleteWarningSuara');
        const warnBebas = document.getElementById('deleteWarningBebas');
        const btnConfirm = document.getElementById('btnConfirmDelete');

        if (suara > 0) {
          warnSuara.style.display = 'block';
          warnBebas.style.display = 'none';
          btnConfirm.textContent = 'Nonaktifkan Kontestan';
        } else {
          warnSuara.style.display = 'none';
          warnBebas.style.display = 'block';
          btnConfirm.textContent = 'Hapus Permanen';
        }

        if (deleteModal) deleteModal.show();
      });
    });
  });
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
