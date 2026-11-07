<?php
/**
 * Manajemen Akun Pemilih (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Manajemen Pemilih';
$breadcrumb_title = 'Daftar Hak Suara Pemilih';
$admin_active = 'pemilih';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$filter_status = trim($_GET['status'] ?? 'all');
$search = trim($_GET['q'] ?? '');

$voters_list = [];
$active_periode = get_active_periode($pdo);
$p_id = $active_periode['id'] ?? 0;

$tot_pemilih = 0;
$tot_voted_kakang = 0;
$tot_voted_senduk = 0;

if ($pdo) {
    try {
        $tot_pemilih = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pemilih'")->fetchColumn();
        if ($p_id > 0) {
            $tot_voted_kakang = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM voting WHERE periode_voting_id = {$p_id} AND kategori = 'Kakang'")->fetchColumn();
            $tot_voted_senduk = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM voting WHERE periode_voting_id = {$p_id} AND kategori = 'Senduk'")->fetchColumn();
        }

        $sql = "
            SELECT 
                u.id, u.nama, u.email, u.no_hp, u.status, u.created_at,
                (SELECT COUNT(*) FROM voting v WHERE v.user_id = u.id AND v.periode_voting_id = :p_id AND v.kategori = 'Kakang') AS vote_kakang,
                (SELECT COUNT(*) FROM voting v WHERE v.user_id = u.id AND v.periode_voting_id = :p_id AND v.kategori = 'Senduk') AS vote_senduk
            FROM users u
            WHERE u.role = 'pemilih'
        ";
        $params = [':p_id' => $p_id];

        if (in_array($filter_status, ['aktif', 'nonaktif'])) {
            $sql .= " AND u.status = :status";
            $params[':status'] = $filter_status;
        }

        if (!empty($search)) {
            $sql .= " AND (u.nama LIKE :kw OR u.email LIKE :kw OR u.no_hp LIKE :kw)";
            $params[':kw'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY u.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $voters_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in admin pemilih.php: " . $e->getMessage());
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
          <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Total Akun Pemilih</small>
          <span class="fs-4 fw-bold text-navy"><?= number_format($tot_pemilih, 0, ',', '.') ?> Akun</span>
        </div>
        <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary fs-4 px-3">
          <i class="bi bi-person-lines-fill"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center justify-content-between">
        <div>
          <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Partisipasi Bilik Kakang</small>
          <span class="fs-4 fw-bold text-navy"><?= $tot_voted_kakang ?> / <?= $tot_pemilih ?></span>
        </div>
        <div class="p-2 rounded-3 bg-navy text-gold fs-4 px-3">
          <i class="bi bi-gender-male"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center justify-content-between">
        <div>
          <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Partisipasi Bilik Senduk</small>
          <span class="fs-4 fw-bold" style="color: #A67C2E;"><?= $tot_voted_senduk ?> / <?= $tot_pemilih ?></span>
        </div>
        <div class="p-2 rounded-3 text-white fs-4 px-3" style="background: linear-gradient(135deg, #4A154B, #220B24);">
          <i class="bi bi-gender-female"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <h5 class="admin-card-title"><i class="bi bi-person-check-fill text-gold"></i> Database Akun Pemilih Masyarakat</h5>
        <small class="text-muted">Total: <?= count($voters_list) ?> akun ditemukan dalam sistem</small>
      </div>
      <div>
        <span class="badge bg-light text-navy border px-3 py-2 rounded-pill font-mono">
          <i class="bi bi-shield-lock-fill text-success me-1"></i> BCRYPT HASH PROTECTED
        </span>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="p-3 bg-light border-bottom">
      <form action="" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Semua Status Akun</option>
            <option value="aktif" <?= $filter_status === 'aktif' ? 'selected' : '' ?>>Hanya Akun Aktif</option>
            <option value="nonaktif" <?= $filter_status === 'nonaktif' ? 'selected' : '' ?>>Akun Diblokir / Nonaktif</option>
          </select>
        </div>
        <div class="col-md-6">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control border-start-0" placeholder="Cari nama pemilih, email, nomor telepon..." value="<?= escape($search) ?>">
            <button class="btn btn-navy" type="submit">Cari</button>
          </div>
        </div>
        <div class="col-md-3 text-md-end">
          <a href="<?= BASE_URL ?>admin/pemilih.php" class="btn btn-outline-secondary btn-sm w-100">Reset Filter</a>
        </div>
      </form>
    </div>

    <div class="admin-card-body p-0">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th style="width: 70px;">ID</th>
              <th>Nama Pemilih</th>
              <th>Informasi Kontak</th>
              <th>Tanggal Registrasi</th>
              <th>Status Voting Kakang</th>
              <th>Status Voting Senduk</th>
              <th>Status Akun</th>
              <th class="text-end pe-4" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($voters_list)): ?>
              <?php foreach ($voters_list as $v): ?>
                <tr>
                  <td class="text-muted font-mono small">#<?= $v['id'] ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="admin-sidebar-avatar" style="width: 34px; height: 34px; font-size: 0.8rem;">
                        <?= strtoupper(substr($v['nama'], 0, 1)) ?>
                      </div>
                      <span class="fw-bold text-navy"><?= escape($v['nama']) ?></span>
                    </div>
                  </td>
                  <td>
                    <div class="small text-dark"><?= escape($v['email']) ?></div>
                    <div class="small text-muted font-mono"><i class="bi bi-whatsapp text-success me-1"></i><?= escape($v['no_hp']) ?></div>
                  </td>
                  <td class="text-muted small font-mono">
                    <?= format_tanggal_indo($v['created_at']) ?>
                  </td>
                  <td>
                    <?php if ($v['vote_kakang'] > 0): ?>
                      <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i>Sudah Memilih
                      </span>
                    <?php else: ?>
                      <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">Belum Memilih</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($v['vote_senduk'] > 0): ?>
                      <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i>Sudah Memilih
                      </span>
                    <?php else: ?>
                      <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">Belum Memilih</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($v['status'] === 'aktif'): ?>
                      <span class="badge bg-success px-2 py-1 rounded-pill">Aktif</span>
                    <?php else: ?>
                      <span class="badge bg-danger px-2 py-1 rounded-pill">Diblokir</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end pe-4">
                    <form action="<?= BASE_URL ?>proses/toggle-pemilih.php" method="POST" class="d-inline">
                      <?= csrf_input() ?>
                      <input type="hidden" name="user_id" value="<?= $v['id'] ?>">
                      <?php if ($v['status'] === 'aktif'): ?>
                        <button type="submit" name="action" value="deactivate" class="btn btn-outline-danger btn-sm rounded-pill px-3" title="Blokir Akun" onclick="return confirm('Apakah Anda yakin ingin memblokir akun pemilih ini?');">
                          <i class="bi bi-slash-circle me-1"></i> Blokir
                        </button>
                      <?php else: ?>
                        <button type="submit" name="action" value="activate" class="btn btn-outline-success btn-sm rounded-pill px-3" title="Buka Blokir Akun" onclick="return confirm('Aktifkan kembali akun pemilih ini?');">
                          <i class="bi bi-check-circle me-1"></i> Aktifkan
                        </button>
                      <?php endif; ?>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="bi bi-person-x fs-1 d-block mb-2 text-gold"></i>
                  Tidak ada data pemilih yang cocok dengan kriteria pencarian.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
