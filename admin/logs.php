<?php
/**
 * Audit Trail & Log Aktivitas Sistem (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Audit Trail Logs';
$breadcrumb_title = 'Audit Trail & Rekam Jejak Sistem';
$admin_active = 'logs';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$filter_modul = trim($_GET['modul'] ?? 'all');
$logs_list = [];

if ($pdo) {
    try {
        $sql = "
            SELECT a.*, u.nama AS user_nama, u.email AS user_email, u.role AS user_role
            FROM activity_logs a
            JOIN users u ON a.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($filter_modul !== 'all' && !empty($filter_modul)) {
            $sql .= " AND a.modul = :modul";
            $params[':modul'] = $filter_modul;
        }

        $sql .= " ORDER BY a.id DESC LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in admin logs.php: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <div class="admin-card">
    <div class="admin-card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <h5 class="admin-card-title"><i class="bi bi-shield-shaded text-gold"></i> Audit Trail & Log Keamanan Sistem</h5>
        <small class="text-muted">Menampilkan rekam jejak 100 aktivitas terbaru secara transparan</small>
      </div>
      <div>
        <form action="" method="GET" class="d-flex gap-2">
          <select name="modul" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all" <?= $filter_modul === 'all' ? 'selected' : '' ?>>Semua Modul</option>
            <option value="Autentikasi" <?= $filter_modul === 'Autentikasi' ? 'selected' : '' ?>>Autentikasi</option>
            <option value="Voting" <?= $filter_modul === 'Voting' ? 'selected' : '' ?>>Voting</option>
            <option value="Kontestan" <?= $filter_modul === 'Kontestan' ? 'selected' : '' ?>>Kontestan</option>
            <option value="Pemilih" <?= $filter_modul === 'Pemilih' ? 'selected' : '' ?>>Pemilih</option>
            <option value="Periode" <?= $filter_modul === 'Periode' ? 'selected' : '' ?>>Periode</option>
            <option value="Laporan" <?= $filter_modul === 'Laporan' ? 'selected' : '' ?>>Laporan</option>
          </select>
          <a href="<?= BASE_URL ?>admin/logs.php" class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
      </div>
    </div>

    <div class="admin-card-body p-0">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th style="width: 180px;">Waktu Kejadian</th>
              <th>Pengguna</th>
              <th>Modul</th>
              <th>Deskripsi Aktivitas</th>
              <th>IP Address & Device</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($logs_list)): ?>
              <?php foreach ($logs_list as $l): ?>
                <tr>
                  <td class="text-muted small font-mono">
                    <i class="bi bi-clock me-1 text-gold"></i><?= format_tanggal_indo($l['created_at']) ?>
                  </td>
                  <td>
                    <div class="fw-bold text-navy"><?= escape($l['user_nama']) ?></div>
                    <small class="text-muted font-mono" style="font-size: 0.72rem;"><?= escape($l['user_email']) ?></small>
                    <span class="badge <?= $l['user_role'] === 'admin' ? 'badge-gold-pill' : 'bg-light text-secondary border' ?> ms-1" style="font-size: 0.65rem;">
                      <?= escape($l['user_role']) ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-light text-navy border px-2 py-1"><?= escape($l['modul']) ?></span>
                  </td>
                  <td class="text-secondary fw-medium"><?= escape($l['aktivitas']) ?></td>
                  <td>
                    <div class="font-mono text-muted small"><i class="bi bi-hdd-network me-1"></i><?= escape($l['ip_address']) ?></div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center py-5 text-muted">
                  <i class="bi bi-journal-check fs-1 d-block mb-2 text-gold"></i>
                  Belum ada catatan aktivitas pada modul yang dipilih.
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
