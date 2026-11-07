<?php
/**
 * Halaman Katalog Kontestan
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Daftar Kontestan';
$active_nav = 'kontestan';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Filter parameter dari GET
$kategori_filter = trim($_GET['kategori'] ?? 'all');
$keyword = trim($_GET['q'] ?? '');

$kontestan_list = [];
$total_kakang = 0;
$total_senduk = 0;

if ($pdo) {
    try {
        // Hitung total kontestan per kategori
        $cntKakang = $pdo->query("SELECT COUNT(*) FROM kontestan WHERE kategori = 'Kakang' AND status = 'aktif'")->fetchColumn();
        $cntSenduk = $pdo->query("SELECT COUNT(*) FROM kontestan WHERE kategori = 'Senduk' AND status = 'aktif'")->fetchColumn();
        $total_kakang = (int)$cntKakang;
        $total_senduk = (int)$cntSenduk;

        // Query kontestan aktif
        $sql = "SELECT * FROM kontestan WHERE status = 'aktif'";
        $params = [];

        if (in_array($kategori_filter, ['Kakang', 'Senduk'])) {
            $sql .= " AND kategori = :kategori";
            $params[':kategori'] = $kategori_filter;
        }

        if (!empty($keyword)) {
            $sql .= " AND (nama LIKE :kw OR asal_instansi LIKE :kw OR nomor_peserta LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY nomor_peserta ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $kontestan_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in kontestan.php: " . $e->getMessage());
    }
}
?>

<!-- Banner Header -->
<div class="py-5" style="background: radial-gradient(circle at 80% 20%, #243F73 0%, #101B35 70%); color: white;">
  <div class="container py-3">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="badge" style="background: rgba(214, 174, 88, 0.2); color: #F2D07C; border: 1px solid rgba(214, 174, 88, 0.4);">
          Finalis Duta Wisata Ponorogo 2026
        </span>
        <h1 class="font-serif text-white mt-2 mb-2">Katalog Kontestan</h1>
        <p class="text-light opacity-75 mb-0">Kenali visi, misi, dan potensi para duta muda kebanggaan Kabupaten Ponorogo.</p>
      </div>
      <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <div class="d-inline-flex gap-3 bg-white bg-opacity-10 p-2 rounded-4 border border-white border-opacity-10">
          <div class="px-3 py-1 text-center">
            <span class="d-block fw-bold text-gold fs-5"><?= $total_kakang ?></span>
            <small class="text-light opacity-75" style="font-size: 0.75rem;">Kakang</small>
          </div>
          <div class="vr bg-white opacity-25"></div>
          <div class="px-3 py-1 text-center">
            <span class="d-block fw-bold text-gold fs-5"><?= $total_senduk ?></span>
            <small class="text-light opacity-75" style="font-size: 0.75rem;">Senduk</small>
          </div>
          <div class="vr bg-white opacity-25"></div>
          <div class="px-3 py-1 text-center">
            <span class="d-block fw-bold text-gold fs-5"><?= $total_kakang + $total_senduk ?></span>
            <small class="text-light opacity-75" style="font-size: 0.75rem;">Total Finalis</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Content & Filters -->
<div class="container py-5">
  <div class="row g-4 align-items-center mb-4">
    <!-- Category Tabs -->
    <div class="col-lg-7">
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="filter-tab-btn <?= ($kategori_filter === 'all' || empty($kategori_filter)) ? 'active' : '' ?>" data-filter="all">
          Semua (<?= $total_kakang + $total_senduk ?>)
        </button>
        <button type="button" class="filter-tab-btn <?= ($kategori_filter === 'Kakang') ? 'active' : '' ?>" data-filter="Kakang">
          <i class="bi bi-gender-male me-1"></i> Kakang (<?= $total_kakang ?>)
        </button>
        <button type="button" class="filter-tab-btn <?= ($kategori_filter === 'Senduk') ? 'active' : '' ?>" data-filter="Senduk">
          <i class="bi bi-gender-female me-1"></i> Senduk (<?= $total_senduk ?>)
        </button>
      </div>
    </div>

    <!-- Search Input -->
    <div class="col-lg-5">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" id="searchContestant" class="form-control border-start-0 rounded-end-pill py-2" placeholder="Cari nama, instansi, atau nomor peserta..." value="<?= escape($keyword) ?>">
      </div>
    </div>
  </div>

  <!-- Contestants Grid -->
  <div class="row g-4" id="contestantsContainer">
    <?php if (!empty($kontestan_list)): ?>
      <?php foreach ($kontestan_list as $row): ?>
        <div class="col-xl-3 col-lg-4 col-md-6 contestant-item" 
             data-kategori="<?= escape($row['kategori']) ?>" 
             data-nama="<?= escape($row['nama']) ?>" 
             data-instansi="<?= escape($row['asal_instansi']) ?>" 
             data-nomor="<?= escape($row['nomor_peserta']) ?>">
          <div class="contestant-card">
            <div class="contestant-img-wrapper">
              <span class="contestant-badge-num"><?= escape($row['nomor_peserta']) ?></span>
              <span class="contestant-badge-category <?= ($row['kategori'] === 'Kakang') ? 'badge-kakang' : 'badge-senduk' ?>">
                <?= escape($row['kategori']) ?>
              </span>
              <img src="<?= get_kontestan_photo_url($row['foto'], $row['kategori']) ?>" alt="<?= escape($row['nama']) ?>" class="contestant-img" loading="lazy">
            </div>
            <div class="contestant-info">
              <h5 class="contestant-name"><?= escape($row['nama']) ?></h5>
              <div class="contestant-instansi">
                <i class="bi bi-building text-gold"></i>
                <span class="text-truncate"><?= escape($row['asal_instansi']) ?></span>
              </div>
              <p class="small text-secondary mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4rem;">
                <?= escape($row['visi'] ?: $row['deskripsi']) ?>
              </p>
              <div class="contestant-footer">
                <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary btn-sm flex-fill">
                  <i class="bi bi-person-lines-fill me-1"></i> Detail
                </a>
                <a href="<?= BASE_URL ?>pages/voting.php?pilih=<?= $row['id'] ?>&kat=<?= $row['kategori'] ?>" class="btn btn-gold btn-sm flex-fill">
                  <i class="bi bi-check2-circle me-1"></i> Vote
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Empty State (Jika Pencarian Tidak Ditemukan) -->
  <div id="contestantEmptyState" class="text-center py-5" style="display: <?= empty($kontestan_list) ? 'block' : 'none' ?>;">
    <div class="p-4 rounded-4 d-inline-block bg-white shadow-sm" style="max-width: 450px;">
      <i class="bi bi-search text-muted display-4 mb-3 d-block"></i>
      <h5 class="font-serif text-navy">Tidak Ada Kontestan Ditemukan</h5>
      <p class="text-muted small mb-3">Tidak ada data kontestan yang cocok dengan kata kunci atau filter pencarian Anda.</p>
      <a href="<?= BASE_URL ?>pages/kontestan.php" class="btn btn-outline-gold btn-sm">Reset Pencarian</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

