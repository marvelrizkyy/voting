<?php
/**
 * Halaman Klasemen Perolehan Suara & Podium Juara
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Klasemen Sementara';
$active_nav = 'klasemen';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$active_periode = get_active_periode($pdo);
$p_id = $active_periode ? (int)$active_periode['id'] : 0;

$kakang_ranking = [];
$senduk_ranking = [];
$tot_suara_kakang = 0;
$tot_suara_senduk = 0;

if ($pdo && $p_id > 0) {
    try {
        // Hitung total suara kategori Kakang
        $stmtTotK = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Kakang'");
        $stmtTotK->execute([$p_id]);
        $tot_suara_kakang = (int)$stmtTotK->fetchColumn();

        // Hitung total suara kategori Senduk
        $stmtTotS = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Senduk'");
        $stmtTotS->execute([$p_id]);
        $tot_suara_senduk = (int)$stmtTotS->fetchColumn();

        // Ambil ranking Kakang lengkap
        $stmtK = $pdo->prepare("
            SELECT 
                k.id, k.nomor_peserta, k.nama, k.asal_instansi, k.foto,
                COUNT(v.id) AS total_suara,
                ROUND((COUNT(v.id) / NULLIF(:tot_k, 0)) * 100, 2) AS persentase
            FROM kontestan k
            LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
            WHERE k.kategori = 'Kakang' AND k.status = 'aktif'
            GROUP BY k.id
            ORDER BY total_suara DESC, k.nomor_peserta ASC
        ");
        $stmtK->execute([':tot_k' => $tot_suara_kakang, ':p_id' => $p_id]);
        $kakang_ranking = $stmtK->fetchAll();

        // Ambil ranking Senduk lengkap
        $stmtS = $pdo->prepare("
            SELECT 
                k.id, k.nomor_peserta, k.nama, k.asal_instansi, k.foto,
                COUNT(v.id) AS total_suara,
                ROUND((COUNT(v.id) / NULLIF(:tot_s, 0)) * 100, 2) AS persentase
            FROM kontestan k
            LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
            WHERE k.kategori = 'Senduk' AND k.status = 'aktif'
            GROUP BY k.id
            ORDER BY total_suara DESC, k.nomor_peserta ASC
        ");
        $stmtS->execute([':tot_s' => $tot_suara_senduk, ':p_id' => $p_id]);
        $senduk_ranking = $stmtS->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in klasemen.php: " . $e->getMessage());
    }
}

$active_tab = isset($_GET['tab']) && strtolower($_GET['tab']) === 'senduk' ? 'senduk' : 'kakang';
?>

<!-- Header Banner -->
<div class="py-5" style="background: radial-gradient(circle at 80% 20%, #243F73 0%, #101B35 70%); color: white;">
  <div class="container py-3">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge" style="background: rgba(214, 174, 88, 0.2); color: #F2D07C; border: 1px solid rgba(214, 174, 88, 0.4);">
          Rekapitulasi Suara Resmi • Duta Wisata 2026
        </span>
        <h1 class="font-serif text-white mt-2 mb-2">Klasemen Perolehan Suara</h1>
        <p class="text-light opacity-75 mb-0">
          Perhitungan akumulasi suara masuk secara langsung dari database pemilihan.
        </p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <button type="button" class="btn btn-outline-gold btn-sm rounded-pill px-3" onclick="window.location.reload();">
          <i class="bi bi-arrow-clockwise me-1"></i> Segarkan Data (Refresh)
        </button>
        <div class="small text-light opacity-50 mt-2">
          Terakhir diperbarui: <?= date('d M Y, H:i') ?> WIB
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
  <!-- Disclaimer Box -->
  <div class="alert alert-info border-0 rounded-4 shadow-sm p-3 mb-5 d-flex align-items-center justify-content-between" style="background: rgba(36, 63, 115, 0.08);">
    <div class="d-flex align-items-center">
      <i class="bi bi-info-circle-fill fs-3 text-primary me-3"></i>
      <div>
        <strong class="text-navy">Status Klasemen Sementara:</strong>
        <p class="mb-0 small text-secondary">
          Klasemen bersifat sementara dan terus bergerak dinamis hingga batas akhir periode pemilihan pada <strong><?= $active_periode ? format_tanggal_indo($active_periode['tanggal_selesai']) : '-' ?></strong>.
        </p>
      </div>
    </div>
  </div>

  <!-- Tab Kategori Klasemen -->
  <ul class="nav nav-pills justify-content-center gap-3 mb-5" id="klasemenTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link rounded-pill px-4 py-2 fw-semibold fs-6 <?= ($active_tab === 'kakang') ? 'active bg-navy text-gold' : 'bg-white text-navy border' ?>" 
              id="k-tab" data-bs-toggle="pill" data-bs-target="#pane-kakang" type="button" role="tab" aria-controls="pane-kakang" aria-selected="<?= ($active_tab === 'kakang') ? 'true' : 'false' ?>">
        <i class="bi bi-gender-male me-2"></i> Klasemen Kakang Ponorogo (<?= number_format($tot_suara_kakang, 0, ',', '.') ?> Suara)
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link rounded-pill px-4 py-2 fw-semibold fs-6 <?= ($active_tab === 'senduk') ? 'active bg-navy text-gold' : 'bg-white text-navy border' ?>" 
              id="s-tab" data-bs-toggle="pill" data-bs-target="#pane-senduk" type="button" role="tab" aria-controls="pane-senduk" aria-selected="<?= ($active_tab === 'senduk') ? 'true' : 'false' ?>">
        <i class="bi bi-gender-female me-2"></i> Klasemen Senduk Ponorogo (<?= number_format($tot_suara_senduk, 0, ',', '.') ?> Suara)
      </button>
    </li>
  </ul>

  <!-- Tab Panels -->
  <div class="tab-content" id="klasemenTabContent">

    <!-- 1. KLASEMEN KAKANG -->
    <div class="tab-pane fade <?= ($active_tab === 'kakang') ? 'show active' : '' ?>" id="pane-kakang" role="tabpanel" aria-labelledby="k-tab">
      <!-- PODIUM JUARA 1, 2, 3 KAKANG -->
      <?php if (count($kakang_ranking) >= 3): ?>
        <?php 
          $k1 = $kakang_ranking[0];
          $k2 = $kakang_ranking[1];
          $k3 = $kakang_ranking[2];
        ?>
        <div class="podium-container">
          <!-- Juara 2 (Perak) -->
          <div class="podium-step podium-2">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown" title="Juara 2">🥈</span>
              <img src="<?= get_kontestan_photo_url($k2['foto'], 'Kakang') ?>" alt="<?= escape($k2['nama']) ?>" class="podium-avatar">
            </div>
            <div class="podium-block">
              <span class="podium-rank">2</span>
              <div class="fw-bold small text-truncate px-2 w-100 text-center"><?= escape($k2['nama']) ?></div>
              <small class="badge bg-white text-dark mt-1"><?= number_format($k2['total_suara'], 0, ',', '.') ?> Suara (<?= $k2['persentase'] ?: 0 ?>%)</small>
            </div>
          </div>

          <!-- Juara 1 (Emas) -->
          <div class="podium-step podium-1">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown" title="Juara 1">👑</span>
              <img src="<?= get_kontestan_photo_url($k1['foto'], 'Kakang') ?>" alt="<?= escape($k1['nama']) ?>" class="podium-avatar">
            </div>
            <div class="podium-block">
              <span class="podium-rank">1</span>
              <div class="fw-bold text-truncate px-2 w-100 text-center"><?= escape($k1['nama']) ?></div>
              <small class="badge bg-navy text-gold mt-1 fw-bold"><?= number_format($k1['total_suara'], 0, ',', '.') ?> Suara (<?= $k1['persentase'] ?: 0 ?>%)</small>
            </div>
          </div>

          <!-- Juara 3 (Perunggu) -->
          <div class="podium-step podium-3">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown" title="Juara 3">🥉</span>
              <img src="<?= get_kontestan_photo_url($k3['foto'], 'Kakang') ?>" alt="<?= escape($k3['nama']) ?>" class="podium-avatar">
            </div>
            <div class="podium-block">
              <span class="podium-rank">3</span>
              <div class="fw-bold small text-truncate px-2 w-100 text-center"><?= escape($k3['nama']) ?></div>
              <small class="badge bg-white text-dark mt-1"><?= number_format($k3['total_suara'], 0, ',', '.') ?> Suara (<?= $k3['persentase'] ?: 0 ?>%)</small>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Tabel Klasemen Lengkap Kakang -->
      <div class="card border-0 rounded-4 shadow-sm overflow-hidden bg-white mt-4">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead style="background: #F8FAFC;">
              <tr>
                <th class="text-center py-3" style="width: 70px;">Rank</th>
                <th class="py-3">Kontestan</th>
                <th class="py-3">Asal Sekolah / Instansi</th>
                <th class="py-3" style="width: 250px;">Persentase</th>
                <th class="text-end py-3 pe-4" style="width: 140px;">Jumlah Suara</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($kakang_ranking)): ?>
                <?php foreach ($kakang_ranking as $idx => $row): ?>
                  <?php 
                    $rank = $idx + 1;
                    $pct = $row['persentase'] ?: 0;
                  ?>
                  <tr>
                    <td class="text-center">
                      <?php if ($rank === 1): ?>
                        <span class="badge rounded-circle p-2 bg-warning text-dark fs-6" title="Peringkat 1">🥇</span>
                      <?php elseif ($rank === 2): ?>
                        <span class="badge rounded-circle p-2 bg-secondary text-white fs-6" title="Peringkat 2">🥈</span>
                      <?php elseif ($rank === 3): ?>
                        <span class="badge rounded-circle p-2 text-white fs-6" style="background: #CD7F32;" title="Peringkat 3">🥉</span>
                      <?php else: ?>
                        <span class="fw-bold text-muted"><?= $rank ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= get_kontestan_photo_url($row['foto'], 'Kakang') ?>" alt="<?= escape($row['nama']) ?>" class="rounded-circle object-fit-cover" style="width: 44px; height: 44px;">
                        <div>
                          <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $row['id'] ?>" class="fw-bold text-navy text-decoration-none">
                            <?= escape($row['nama']) ?>
                          </a>
                          <small class="d-block text-muted"><?= escape($row['nomor_peserta']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="text-secondary"><?= escape($row['asal_instansi']) ?></span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 10px; border-radius: 6px; background-color: #E2E8F0;">
                          <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #243F73, #D6AE58);" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="small fw-bold text-navy" style="width: 48px;"><?= $pct ?>%</span>
                      </div>
                    </td>
                    <td class="text-end pe-4">
                      <span class="fw-bold text-navy fs-6"><?= number_format($row['total_suara'], 0, ',', '.') ?></span>
                      <small class="text-muted d-block" style="font-size: 0.72rem;">suara sah</small>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">Belum ada kontestan Kakang terdaftar.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 2. KLASEMEN SENDUK -->
    <div class="tab-pane fade <?= ($active_tab === 'senduk') ? 'show active' : '' ?>" id="pane-senduk" role="tabpanel" aria-labelledby="s-tab">
      <!-- PODIUM JUARA 1, 2, 3 SENDUK -->
      <?php if (count($senduk_ranking) >= 3): ?>
        <?php 
          $s1 = $senduk_ranking[0];
          $s2 = $senduk_ranking[1];
          $s3 = $senduk_ranking[2];
        ?>
        <div class="podium-container">
          <!-- Juara 2 (Perak) -->
          <div class="podium-step podium-2">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown" title="Juara 2">🥈</span>
              <img src="<?= get_kontestan_photo_url($s2['foto'], 'Senduk') ?>" alt="<?= escape($s2['nama']) ?>" class="podium-avatar">
            </div>
            <div class="podium-block">
              <span class="podium-rank">2</span>
              <div class="fw-bold small text-truncate px-2 w-100 text-center"><?= escape($s2['nama']) ?></div>
              <small class="badge bg-white text-dark mt-1"><?= number_format($s2['total_suara'], 0, ',', '.') ?> Suara (<?= $s2['persentase'] ?: 0 ?>%)</small>
            </div>
          </div>

          <!-- Juara 1 (Emas) -->
          <div class="podium-step podium-1">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown" title="Juara 1">👑</span>
              <img src="<?= get_kontestan_photo_url($s1['foto'], 'Senduk') ?>" alt="<?= escape($s1['nama']) ?>" class="podium-avatar">
            </div>
            <div class="podium-block">
              <span class="podium-rank">1</span>
              <div class="fw-bold text-truncate px-2 w-100 text-center"><?= escape($s1['nama']) ?></div>
              <small class="badge bg-navy text-gold mt-1 fw-bold"><?= number_format($s1['total_suara'], 0, ',', '.') ?> Suara (<?= $s1['persentase'] ?: 0 ?>%)</small>
            </div>
          </div>

          <!-- Juara 3 (Perunggu) -->
          <div class="podium-step podium-3">
            <div class="podium-avatar-wrapper">
              <span class="podium-crown" title="Juara 3">🥉</span>
              <img src="<?= get_kontestan_photo_url($s3['foto'], 'Senduk') ?>" alt="<?= escape($s3['nama']) ?>" class="podium-avatar">
            </div>
            <div class="podium-block">
              <span class="podium-rank">3</span>
              <div class="fw-bold small text-truncate px-2 w-100 text-center"><?= escape($s3['nama']) ?></div>
              <small class="badge bg-white text-dark mt-1"><?= number_format($s3['total_suara'], 0, ',', '.') ?> Suara (<?= $s3['persentase'] ?: 0 ?>%)</small>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Tabel Klasemen Lengkap Senduk -->
      <div class="card border-0 rounded-4 shadow-sm overflow-hidden bg-white mt-4">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead style="background: #F8FAFC;">
              <tr>
                <th class="text-center py-3" style="width: 70px;">Rank</th>
                <th class="py-3">Kontestan</th>
                <th class="py-3">Asal Sekolah / Instansi</th>
                <th class="py-3" style="width: 250px;">Persentase</th>
                <th class="text-end py-3 pe-4" style="width: 140px;">Jumlah Suara</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($senduk_ranking)): ?>
                <?php foreach ($senduk_ranking as $idx => $row): ?>
                  <?php 
                    $rank = $idx + 1;
                    $pct = $row['persentase'] ?: 0;
                  ?>
                  <tr>
                    <td class="text-center">
                      <?php if ($rank === 1): ?>
                        <span class="badge rounded-circle p-2 bg-warning text-dark fs-6" title="Peringkat 1">🥇</span>
                      <?php elseif ($rank === 2): ?>
                        <span class="badge rounded-circle p-2 bg-secondary text-white fs-6" title="Peringkat 2">🥈</span>
                      <?php elseif ($rank === 3): ?>
                        <span class="badge rounded-circle p-2 text-white fs-6" style="background: #CD7F32;" title="Peringkat 3">🥉</span>
                      <?php else: ?>
                        <span class="fw-bold text-muted"><?= $rank ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= get_kontestan_photo_url($row['foto'], 'Senduk') ?>" alt="<?= escape($row['nama']) ?>" class="rounded-circle object-fit-cover" style="width: 44px; height: 44px;">
                        <div>
                          <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $row['id'] ?>" class="fw-bold text-navy text-decoration-none">
                            <?= escape($row['nama']) ?>
                          </a>
                          <small class="d-block text-muted"><?= escape($row['nomor_peserta']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="text-secondary"><?= escape($row['asal_instansi']) ?></span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 10px; border-radius: 6px; background-color: #E2E8F0;">
                          <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #A67C2E, #E572B1);" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="small fw-bold text-navy" style="width: 48px;"><?= $pct ?>%</span>
                      </div>
                    </td>
                    <td class="text-end pe-4">
                      <span class="fw-bold text-navy fs-6"><?= number_format($row['total_suara'], 0, ',', '.') ?></span>
                      <small class="text-muted d-block" style="font-size: 0.72rem;">suara sah</small>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">Belum ada kontestan Senduk terdaftar.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

