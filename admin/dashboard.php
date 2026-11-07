<?php
/**
 * Administrator Dashboard (VIP Executive Edition)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Command Center — Dashboard';
$breadcrumb_title = 'Command Center Administrator';
$admin_active = 'dashboard';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

// Metrik Data
$tot_kontestan = 0;
$tot_kakang = 0;
$tot_senduk = 0;
$tot_pemilih = 0;
$tot_suara_kakang = 0;
$tot_suara_senduk = 0;
$tot_suara_all = 0;

$active_periode = get_active_periode($pdo);
$top_kakang = [];
$top_senduk = [];
$recent_activities = [];
$chart_kakang = ['labels' => [], 'votes' => []];
$chart_senduk = ['labels' => [], 'votes' => []];

if ($pdo) {
    try {
        // 1. Metrik Kontestan
        $tot_kakang = (int)$pdo->query("SELECT COUNT(*) FROM kontestan WHERE kategori = 'Kakang'")->fetchColumn();
        $tot_senduk = (int)$pdo->query("SELECT COUNT(*) FROM kontestan WHERE kategori = 'Senduk'")->fetchColumn();
        $tot_kontestan = $tot_kakang + $tot_senduk;

        // 2. Metrik Pemilih
        $tot_pemilih = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pemilih'")->fetchColumn();

        // 3. Metrik Suara & Data Periode Terpilih
        $p_id = $active_periode['id'] ?? 0;
        if ($p_id > 0) {
            $stmtSK = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Kakang'");
            $stmtSK->execute([$p_id]);
            $tot_suara_kakang = (int)$stmtSK->fetchColumn();

            $stmtSS = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Senduk'");
            $stmtSS->execute([$p_id]);
            $tot_suara_senduk = (int)$stmtSS->fetchColumn();

            $tot_suara_all = $tot_suara_kakang + $tot_suara_senduk;

            // Top 3 Kakang Leaderboard
            $stmtTopK = $pdo->prepare("
                SELECT k.id, k.nomor_peserta, k.nama, k.asal_instansi, k.foto, COUNT(v.id) AS total_suara
                FROM kontestan k
                LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
                WHERE k.kategori = 'Kakang' AND k.status = 'aktif'
                GROUP BY k.id
                ORDER BY total_suara DESC, k.nomor_peserta ASC
                LIMIT 3
            ");
            $stmtTopK->execute([':p_id' => $p_id]);
            $top_kakang = $stmtTopK->fetchAll();

            // Top 3 Senduk Leaderboard
            $stmtTopS = $pdo->prepare("
                SELECT k.id, k.nomor_peserta, k.nama, k.asal_instansi, k.foto, COUNT(v.id) AS total_suara
                FROM kontestan k
                LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
                WHERE k.kategori = 'Senduk' AND k.status = 'aktif'
                GROUP BY k.id
                ORDER BY total_suara DESC, k.nomor_peserta ASC
                LIMIT 3
            ");
            $stmtTopS->execute([':p_id' => $p_id]);
            $top_senduk = $stmtTopS->fetchAll();

            // Data Grafik Bar Kakang
            $stmtGK = $pdo->prepare("
                SELECT k.nomor_peserta, k.nama, COUNT(v.id) AS total_suara
                FROM kontestan k
                LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
                WHERE k.kategori = 'Kakang' AND k.status = 'aktif'
                GROUP BY k.id
                ORDER BY k.nomor_peserta ASC
            ");
            $stmtGK->execute([':p_id' => $p_id]);
            while ($row = $stmtGK->fetch()) {
                $chart_kakang['labels'][] = $row['nomor_peserta'] . ' ' . explode(' ', $row['nama'])[0];
                $chart_kakang['votes'][] = (int)$row['total_suara'];
            }

            // Data Grafik Bar Senduk
            $stmtGS = $pdo->prepare("
                SELECT k.nomor_peserta, k.nama, COUNT(v.id) AS total_suara
                FROM kontestan k
                LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
                WHERE k.kategori = 'Senduk' AND k.status = 'aktif'
                GROUP BY k.id
                ORDER BY k.nomor_peserta ASC
            ");
            $stmtGS->execute([':p_id' => $p_id]);
            while ($row = $stmtGS->fetch()) {
                $chart_senduk['labels'][] = $row['nomor_peserta'] . ' ' . explode(' ', $row['nama'])[0];
                $chart_senduk['votes'][] = (int)$row['total_suara'];
            }
        }

        // 4. Log Aktivitas Sistem Terkini
        $stmtLogs = $pdo->query("
            SELECT a.*, u.nama AS user_nama, u.role AS user_role
            FROM activity_logs a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.id DESC
            LIMIT 6
        ");
        $recent_activities = $stmtLogs->fetchAll();

    } catch (PDOException $e) {
        error_log("Error in admin dashboard: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="admin-content">
  <?= display_flash() ?>

  <!-- 1. VIP EXECUTIVE HERO BANNER -->
  <div class="admin-hero-banner">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="hero-gold-badge mb-2">
          <i class="bi bi-shield-check"></i>
          <span>Official Pageant Command Center • Ponorogo 2026</span>
        </div>
        <h2 class="admin-hero-title">
          Bumi Reog <span>Pengawasan Digital</span>
        </h2>
        <p class="admin-hero-desc">
          Panel terpadu pemantauan perolehan suara sah, manajemen kontestan, dan integritas data pemilihan Duta Wisata Daerah Kabupaten Ponorogo secara *real-time*.
        </p>

        <div class="d-flex flex-wrap gap-3">
          <a href="<?= BASE_URL ?>admin/tambah-kontestan.php" class="hero-action-btn">
            <i class="bi bi-person-plus-fill"></i> Tambah Kontestan Baru
          </a>
          <a href="<?= BASE_URL ?>admin/hasil-voting.php" class="hero-action-btn-outline">
            <i class="bi bi-bar-chart-line"></i> Rekapitulasi Suara
          </a>
          <a href="<?= BASE_URL ?>admin/laporan.php" class="hero-action-btn-outline">
            <i class="bi bi-file-earmark-pdf"></i> Ekspor Laporan
          </a>
        </div>
      </div>

      <!-- Quick Status Box Periode -->
      <div class="col-lg-4 text-lg-end">
        <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-15 text-start d-inline-block w-100" style="max-width: 340px;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-gold fw-bold text-uppercase">Status Periode Aktif:</span>
            <?php if ($active_periode && $active_periode['status'] === 'aktif'): ?>
              <span class="badge bg-success px-2 py-1"><i class="bi bi-broadcast me-1"></i>Buka</span>
            <?php else: ?>
              <span class="badge bg-danger px-2 py-1"><i class="bi bi-pause-circle me-1"></i>Tutup</span>
            <?php endif; ?>
          </div>
          <h6 class="text-white fw-bold font-serif mb-1 text-truncate">
            <?= escape($active_periode['nama_periode'] ?? 'Belum ada periode aktif') ?>
          </h6>
          <small class="text-light opacity-75 d-block mb-3 font-mono" style="font-size: 0.75rem;">
            <?= $active_periode ? format_tanggal_indo($active_periode['tanggal_mulai'], false) . ' — ' . format_tanggal_indo($active_periode['tanggal_selesai'], false) : '-' ?>
          </small>
          <a href="<?= BASE_URL ?>admin/periode.php" class="btn btn-outline-light btn-sm w-100 py-1" style="font-size: 0.8rem;">
            <i class="bi bi-sliders me-1"></i> Atur Jadwal & Status Periode
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. EXECUTIVE KPI METRICS (4 CARDS) -->
  <div class="row g-4 mb-4">
    <!-- Total Kontestan -->
    <div class="col-xl-3 col-sm-6">
      <div class="kpi-card kpi-primary">
        <div class="kpi-header">
          <div>
            <span class="kpi-label">Total Kontestan</span>
            <div class="kpi-number"><?= $tot_kontestan ?></div>
          </div>
          <div class="kpi-icon-wrapper icon-wrap-primary">
            <i class="bi bi-people-fill"></i>
          </div>
        </div>
        <div class="kpi-subtext pt-2 border-top">
          <span class="badge badge-kakang-pill"><?= $tot_kakang ?> Kakang</span>
          <span class="badge badge-senduk-pill"><?= $tot_senduk ?> Senduk</span>
        </div>
      </div>
    </div>

    <!-- Pemilih Terdaftar -->
    <div class="col-xl-3 col-sm-6">
      <div class="kpi-card kpi-purple">
        <div class="kpi-header">
          <div>
            <span class="kpi-label">Pemilih Terverifikasi</span>
            <div class="kpi-number"><?= number_format($tot_pemilih, 0, ',', '.') ?></div>
          </div>
          <div class="kpi-icon-wrapper icon-wrap-purple">
            <i class="bi bi-person-check-fill"></i>
          </div>
        </div>
        <div class="kpi-subtext pt-2 border-top">
          <i class="bi bi-shield-lock-fill text-success"></i>
          <span class="text-success fw-bold">Akun Terenkripsi Aman</span>
        </div>
      </div>
    </div>

    <!-- Total Suara Masuk -->
    <div class="col-xl-3 col-sm-6">
      <div class="kpi-card kpi-gold">
        <div class="kpi-header">
          <div>
            <span class="kpi-label">Akumulasi Suara Masuk</span>
            <div class="kpi-number text-navy"><?= number_format($tot_suara_all, 0, ',', '.') ?></div>
          </div>
          <div class="kpi-icon-wrapper icon-wrap-gold">
            <i class="bi bi-award-fill"></i>
          </div>
        </div>
        <div class="kpi-subtext pt-2 border-top">
          <i class="bi bi-check-all text-gold"></i>
          <span>Database Voting Sah</span>
        </div>
      </div>
    </div>

    <!-- Proporsi Suara Kakang & Senduk -->
    <div class="col-xl-3 col-sm-6">
      <div class="kpi-card kpi-success">
        <div class="kpi-header">
          <div>
            <span class="kpi-label">Distribusi Kategori</span>
            <div class="kpi-number" style="font-size: 1.7rem;">
              <?= $tot_suara_kakang ?> <span class="text-muted fs-5">/</span> <?= $tot_suara_senduk ?>
            </div>
          </div>
          <div class="kpi-icon-wrapper icon-wrap-success">
            <i class="bi bi-pie-chart-fill"></i>
          </div>
        </div>
        <div class="kpi-subtext pt-2 border-top">
          <span class="text-primary fw-bold"><?= $tot_suara_kakang ?> Kakang</span>
          <span>•</span>
          <span class="text-dark fw-bold"><?= $tot_suara_senduk ?> Senduk</span>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. LIVE LEADERBOARD PREVIEW (TOP 3 KAKANG & SENDUK) -->
  <div class="row g-4 mb-4">
    <!-- Top Kakang -->
    <div class="col-lg-6">
      <div class="admin-card">
        <div class="admin-card-header">
          <h5 class="admin-card-title">
            <i class="bi bi-trophy-fill text-gold"></i> Top 3 Klasemen Kakang Sementara
          </h5>
          <a href="<?= BASE_URL ?>admin/hasil-voting.php?kat=Kakang" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="admin-card-body">
          <?php if (!empty($top_kakang)): ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($top_kakang as $idx => $tk): ?>
                <?php 
                  $pct = $tot_suara_kakang > 0 ? round(($tk['total_suara'] / $tot_suara_kakang) * 100, 1) : 0;
                  $crown = ($idx === 0) ? '👑' : (($idx === 1) ? '🥈' : '🥉');
                  $rank_color = ($idx === 0) ? '#D6AE58' : (($idx === 1) ? '#64748B' : '#CD7F32');
                ?>
                <div class="leader-card-item">
                  <div class="leader-avatar-wrapper">
                    <img src="<?= get_kontestan_photo_url($tk['foto'], 'Kakang') ?>" alt="" class="leader-avatar">
                    <span class="leader-rank-crown"><?= $crown ?></span>
                  </div>
                  <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <div class="fw-bold text-navy text-truncate"><?= escape($tk['nama']) ?></div>
                      <span class="badge bg-navy text-gold font-mono small"><?= $tk['total_suara'] ?> Vote (<?= $pct ?>%)</span>
                    </div>
                    <small class="text-muted d-block text-truncate mb-2"><?= escape($tk['nomor_peserta']) ?> • <?= escape($tk['asal_instansi']) ?></small>
                    <div class="progress" style="height: 6px; border-radius: 4px;">
                      <div class="progress-bar bg-gold" style="width: <?= $pct ?>%;"></div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted small text-center py-4 mb-0">Belum ada suara untuk kategori Kakang.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Top Senduk -->
    <div class="col-lg-6">
      <div class="admin-card">
        <div class="admin-card-header">
          <h5 class="admin-card-title">
            <i class="bi bi-trophy-fill text-pink" style="color: #E572B1;"></i> Top 3 Klasemen Senduk Sementara
          </h5>
          <a href="<?= BASE_URL ?>admin/hasil-voting.php?kat=Senduk" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="admin-card-body">
          <?php if (!empty($top_senduk)): ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($top_senduk as $idx => $ts): ?>
                <?php 
                  $pct = $tot_suara_senduk > 0 ? round(($ts['total_suara'] / $tot_suara_senduk) * 100, 1) : 0;
                  $crown = ($idx === 0) ? '👑' : (($idx === 1) ? '🥈' : '🥉');
                ?>
                <div class="leader-card-item">
                  <div class="leader-avatar-wrapper">
                    <img src="<?= get_kontestan_photo_url($ts['foto'], 'Senduk') ?>" alt="" class="leader-avatar">
                    <span class="leader-rank-crown"><?= $crown ?></span>
                  </div>
                  <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <div class="fw-bold text-navy text-truncate"><?= escape($ts['nama']) ?></div>
                      <span class="badge bg-navy text-gold font-mono small"><?= $ts['total_suara'] ?> Vote (<?= $pct ?>%)</span>
                    </div>
                    <small class="text-muted d-block text-truncate mb-2"><?= escape($ts['nomor_peserta']) ?> • <?= escape($ts['asal_instansi']) ?></small>
                    <div class="progress" style="height: 6px; border-radius: 4px;">
                      <div class="progress-bar" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #A67C2E, #E572B1);"></div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted small text-center py-4 mb-0">Belum ada suara untuk kategori Senduk.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- 4. GRAFIK STATISTIK CHART.JS -->
  <div class="row g-4 mb-4">
    <!-- Chart Kakang -->
    <div class="col-lg-6">
      <div class="admin-card">
        <div class="admin-card-header">
          <h5 class="admin-card-title">
            <i class="bi bi-bar-chart-fill text-primary"></i> Distribusi Suara Kontestan Kakang
          </h5>
          <span class="badge bg-primary text-white font-mono"><?= $tot_suara_kakang ?> Suara</span>
        </div>
        <div class="admin-card-body" style="height: 320px;">
          <canvas id="chartKakang"></canvas>
        </div>
      </div>
    </div>

    <!-- Chart Senduk -->
    <div class="col-lg-6">
      <div class="admin-card">
        <div class="admin-card-header">
          <h5 class="admin-card-title">
            <i class="bi bi-bar-chart-fill text-warning"></i> Distribusi Suara Kontestan Senduk
          </h5>
          <span class="badge bg-warning text-dark font-mono"><?= $tot_suara_senduk ?> Suara</span>
        </div>
        <div class="admin-card-body" style="height: 320px;">
          <canvas id="chartSenduk"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- 5. AUDIT TRAIL AKTIVITAS SISTEM -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h5 class="admin-card-title">
        <i class="bi bi-journal-text text-gold"></i> Log Audit Trail Aktivitas Sistem Terkini
      </h5>
      <a href="<?= BASE_URL ?>admin/logs.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        Buka Semua Log <i class="bi bi-arrow-right ms-1"></i>
      </a>
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
              <th>IP Address</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($recent_activities)): ?>
              <?php foreach ($recent_activities as $log): ?>
                <tr>
                  <td class="text-muted small font-mono">
                    <i class="bi bi-clock me-1 text-gold"></i><?= format_tanggal_indo($log['created_at']) ?>
                  </td>
                  <td>
                    <div class="fw-bold text-navy"><?= escape($log['user_nama']) ?></div>
                    <span class="badge <?= $log['user_role'] === 'admin' ? 'badge-gold-pill' : 'bg-light text-secondary border' ?>" style="font-size: 0.68rem;">
                      <?= escape($log['user_role']) ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-light text-navy border px-2 py-1"><?= escape($log['modul']) ?></span>
                  </td>
                  <td class="text-secondary"><?= escape($log['aktivitas']) ?></td>
                  <td class="font-mono text-muted small"><?= escape($log['ip_address']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">Belum ada catatan aktivitas.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  window.dashboardChartData = {
    kakang: <?= json_encode($chart_kakang) ?>,
    senduk: <?= json_encode($chart_senduk) ?>
  };
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
