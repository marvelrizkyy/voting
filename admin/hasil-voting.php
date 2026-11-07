<?php
/**
 * Rekapitulasi Hasil Voting Terpadu (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Hasil & Rekapitulasi Voting';
$breadcrumb_title = 'Rekapitulasi Suara Sah';
$admin_active = 'hasil';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$all_periodes = [];
$selected_periode_id = isset($_GET['periode_id']) ? (int)$_GET['periode_id'] : 0;
$filter_kategori = trim($_GET['kat'] ?? 'all');

$results = [];
$total_kakang = 0;
$total_senduk = 0;
$total_keseluruhan = 0;
$current_periode = null;

if ($pdo) {
    try {
        $all_periodes = $pdo->query("SELECT id, nama_periode, status FROM periode_voting ORDER BY id DESC")->fetchAll();

        if ($selected_periode_id <= 0) {
            $active = get_active_periode($pdo);
            $selected_periode_id = $active ? (int)$active['id'] : (!empty($all_periodes) ? (int)$all_periodes[0]['id'] : 0);
        }

        if ($selected_periode_id > 0) {
            $stmtCurP = $pdo->prepare("SELECT * FROM periode_voting WHERE id = ? LIMIT 1");
            $stmtCurP->execute([$selected_periode_id]);
            $current_periode = $stmtCurP->fetch();

            $stmtTotK = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Kakang'");
            $stmtTotK->execute([$selected_periode_id]);
            $total_kakang = (int)$stmtTotK->fetchColumn();

            $stmtTotS = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Senduk'");
            $stmtTotS->execute([$selected_periode_id]);
            $total_senduk = (int)$stmtTotS->fetchColumn();

            $total_keseluruhan = $total_kakang + $total_senduk;

            $sql = "
                SELECT 
                    k.id, k.nomor_peserta, k.nama, k.kategori, k.asal_instansi, k.foto, k.status,
                    COUNT(v.id) AS total_suara,
                    CASE 
                        WHEN k.kategori = 'Kakang' THEN ROUND((COUNT(v.id) / NULLIF(:tot_k, 0)) * 100, 2)
                        ELSE ROUND((COUNT(v.id) / NULLIF(:tot_s, 0)) * 100, 2)
                    END AS persentase
                FROM kontestan k
                LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
                WHERE 1=1
            ";
            $params = [
                ':tot_k' => $total_kakang,
                ':tot_s' => $total_senduk,
                ':p_id'  => $selected_periode_id
            ];

            if (in_array($filter_kategori, ['Kakang', 'Senduk'])) {
                $sql .= " AND k.kategori = :filter_kat";
                $params[':filter_kat'] = $filter_kategori;
            }

            $sql .= " GROUP BY k.id ORDER BY k.kategori ASC, total_suara DESC, k.nomor_peserta ASC";
            $stmtRes = $pdo->prepare($sql);
            $stmtRes->execute($params);
            $results = $stmtRes->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error in admin hasil-voting.php: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <?= display_flash() ?>

  <!-- Filter Card -->
  <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white p-4 border">
    <form action="" method="GET" class="row g-3 align-items-center">
      <div class="col-lg-5 col-md-6">
        <label for="periode_id" class="form-label small fw-bold text-muted mb-1">Pilih Periode Pemilihan</label>
        <select name="periode_id" id="periode_id" class="form-select" onchange="this.form.submit()">
          <?php foreach ($all_periodes as $pr): ?>
            <option value="<?= $pr['id'] ?>" <?= ($pr['id'] == $selected_periode_id) ? 'selected' : '' ?>>
              <?= escape($pr['nama_periode']) ?> <?= ($pr['status'] === 'aktif') ? '• [Sedang Berjalan]' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-lg-4 col-md-4">
        <label for="kat" class="form-label small fw-bold text-muted mb-1">Filter Kategori</label>
        <select name="kat" id="kat" class="form-select" onchange="this.form.submit()">
          <option value="all" <?= ($filter_kategori === 'all') ? 'selected' : '' ?>>Semua Kategori (Kakang & Senduk)</option>
          <option value="Kakang" <?= ($filter_kategori === 'Kakang') ? 'selected' : '' ?>>Kategori Kakang Saja</option>
          <option value="Senduk" <?= ($filter_kategori === 'Senduk') ? 'selected' : '' ?>>Kategori Senduk Saja</option>
        </select>
      </div>

      <div class="col-lg-3 col-md-2 text-md-end pt-md-4">
        <a href="<?= BASE_URL ?>admin/laporan.php?periode_id=<?= $selected_periode_id ?>" class="btn btn-gold-admin w-100">
          <i class="bi bi-file-earmark-pdf me-1"></i> Buka Laporan Resmi
        </a>
      </div>
    </form>
  </div>

  <!-- Summary KPI Bar -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="kpi-card kpi-gold">
        <div class="kpi-header">
          <div>
            <span class="kpi-label">Akumulasi Seluruh Suara</span>
            <div class="kpi-number text-navy"><?= number_format($total_keseluruhan, 0, ',', '.') ?></div>
          </div>
          <div class="kpi-icon-wrapper icon-wrap-gold">
            <i class="bi bi-check2-all"></i>
          </div>
        </div>
        <div class="kpi-subtext pt-2 border-top">
          <span>Periode: <strong><?= escape($current_periode['nama_periode'] ?? '-') ?></strong></span>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="kpi-card kpi-primary">
        <div class="kpi-header">
          <div>
            <span class="kpi-label">Total Suara Kakang</span>
            <div class="kpi-number text-primary"><?= number_format($total_kakang, 0, ',', '.') ?></div>
          </div>
          <div class="kpi-icon-wrapper icon-wrap-primary">
            <i class="bi bi-gender-male"></i>
          </div>
        </div>
        <div class="kpi-subtext pt-2 border-top">
          <span>Persentase Sah Terhadap Total</span>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="kpi-card kpi-purple">
        <div class="kpi-header">
          <div>
            <span class="kpi-label">Total Suara Senduk</span>
            <div class="kpi-number" style="color: #A67C2E;"><?= number_format($total_senduk, 0, ',', '.') ?></div>
          </div>
          <div class="kpi-icon-wrapper icon-wrap-purple">
            <i class="bi bi-gender-female"></i>
          </div>
        </div>
        <div class="kpi-subtext pt-2 border-top">
          <span>Persentase Sah Terhadap Total</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabel Hasil Lengkap -->
  <div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
      <h5 class="admin-card-title">
        <i class="bi bi-bar-chart-fill text-gold"></i>
        Rekapitulasi Suara Sah: <?= escape($current_periode['nama_periode'] ?? '-') ?>
      </h5>
      <span class="badge bg-light text-navy border px-3 py-2 font-mono">
        <i class="bi bi-clock me-1 text-gold"></i><?= date('d M Y, H:i') ?> WIB
      </span>
    </div>

    <div class="admin-card-body p-0">
      <div class="table-responsive">
        <table class="table table-custom mb-0">
          <thead>
            <tr>
              <th style="width: 75px;" class="text-center">Rank</th>
              <th style="width: 100px;">No. Peserta</th>
              <th>Nama Lengkap Kontestan</th>
              <th>Kategori</th>
              <th>Asal Sekolah / Instansi</th>
              <th style="width: 250px;">Persentase Suara</th>
              <th class="text-end pe-4" style="width: 150px;">Jumlah Suara Sah</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($results)): ?>
              <?php 
                $current_kat = '';
                $rank_counter = 0;
              ?>
              <?php foreach ($results as $row): ?>
                <?php 
                  if ($row['kategori'] !== $current_kat) {
                      $current_kat = $row['kategori'];
                      $rank_counter = 1;
                  } else {
                      $rank_counter++;
                  }
                  $pct = $row['persentase'] ?: 0;
                ?>
                <tr>
                  <td class="text-center">
                    <?php if ($rank_counter === 1): ?>
                      <span class="badge bg-warning text-dark font-mono px-2 py-1 fs-6">1 👑</span>
                    <?php elseif ($rank_counter === 2): ?>
                      <span class="badge bg-secondary font-mono px-2 py-1 fs-6">2 🥈</span>
                    <?php elseif ($rank_counter === 3): ?>
                      <span class="badge font-mono text-white px-2 py-1 fs-6" style="background: #CD7F32;">3 🥉</span>
                    <?php else: ?>
                      <span class="text-muted font-mono fw-bold"><?= $rank_counter ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge bg-navy text-gold font-mono fs-6 px-2 py-1"><?= escape($row['nomor_peserta']) ?></span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?= get_kontestan_photo_url($row['foto'], $row['kategori']) ?>" alt="" class="rounded-3 object-fit-cover shadow-sm border border-gold" style="width: 44px; height: 55px;">
                      <div>
                        <span class="fw-bold text-navy fs-6"><?= escape($row['nama']) ?></span>
                        <small class="text-muted d-block"><?= escape($row['asal_instansi']) ?></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge <?= $row['kategori'] === 'Kakang' ? 'badge-kakang-pill' : 'badge-senduk-pill' ?>">
                      <?= escape($row['kategori']) ?>
                    </span>
                  </td>
                  <td class="text-secondary small fw-medium"><?= escape($row['asal_instansi']) ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="progress flex-grow-1" style="height: 10px; border-radius: 6px; background-color: #E2E8F0;">
                        <div class="progress-bar <?= ($row['kategori'] === 'Kakang') ? 'bg-gold' : '' ?>" 
                             role="progressbar" 
                             style="width: <?= $pct ?>%; <?= ($row['kategori'] === 'Senduk') ? 'background: linear-gradient(90deg, #A67C2E, #E572B1);' : '' ?>" 
                             aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <span class="small font-mono fw-bold text-navy" style="width: 50px; text-align: right;"><?= $pct ?>%</span>
                    </div>
                  </td>
                  <td class="text-end pe-4">
                    <span class="fw-bold text-navy fs-6 font-mono"><?= number_format($row['total_suara'], 0, ',', '.') ?></span>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">vote sah</small>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-1 d-block mb-2 text-gold"></i>
                  Belum ada data perolehan suara untuk periode pemilihan ini.
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
