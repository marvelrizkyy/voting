<?php
/**
 * Bilik Suara Digital (Voting Kakang & Senduk)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Bilik Voting Digital';
$active_nav = 'voting';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Pastikan pengguna masuk jika hendak voting
if (!is_logged_in()) {
    set_flash('warning', 'Silakan masuk (login) terlebih dahulu menggunakan akun pemilih Anda untuk memberikan suara.');
    redirect(BASE_URL . 'auth/login.php');
}

// Administrator dialihkan ke dashboard
if (is_admin()) {
    set_flash('info', 'Administrator bertindak sebagai pengawas netral dan tidak diperkenankan memberikan suara.');
    redirect(BASE_URL . 'admin/dashboard.php');
}

$user_id = (int)$_SESSION['user_id'];
$active_tab = isset($_GET['tab']) && in_array(strtolower($_GET['tab']), ['kakang', 'senduk']) ? strtolower($_GET['tab']) : 'kakang';

$active_periode = get_active_periode($pdo);
$vote_status = get_user_vote_status($pdo, $user_id, $active_periode['id'] ?? null);

// Ambil daftar kontestan Kakang dan Senduk
$kakang_list = [];
$senduk_list = [];

if ($pdo) {
    try {
        $stmtK = $pdo->prepare("SELECT * FROM kontestan WHERE kategori = 'Kakang' AND status = 'aktif' ORDER BY nomor_peserta ASC");
        $stmtK->execute();
        $kakang_list = $stmtK->fetchAll();

        $stmtS = $pdo->prepare("SELECT * FROM kontestan WHERE kategori = 'Senduk' AND status = 'aktif' ORDER BY nomor_peserta ASC");
        $stmtS->execute();
        $senduk_list = $stmtS->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in voting.php: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<!-- Header Banner -->
<div class="py-5" style="background: radial-gradient(circle at 80% 20%, #243F73 0%, #101B35 70%); color: white;">
  <div class="container py-3">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <span class="badge" style="background: rgba(214, 174, 88, 0.2); color: #F2D07C; border: 1px solid rgba(214, 174, 88, 0.4);">
          Bilik Suara Resmi • Duta Wisata 2026
        </span>
        <h1 class="font-serif text-white mt-2 mb-2">Bilik Voting Digital</h1>
        <p class="text-light opacity-75 mb-0">
          Selamat datang, <strong><?= escape($_SESSION['user_nama']) ?></strong>! Berikan suara kehormatan Anda untuk menentukan Kakang dan Senduk Favorit Ponorogo.
        </p>
      </div>

      <!-- Indikator Status Hak Suara -->
      <div class="col-lg-5">
        <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-gold fw-bold text-uppercase">Status Hak Suara Anda:</span>
            <?php if ($vote_status['total_suara'] === 2): ?>
              <span class="badge bg-success px-3 py-1"><i class="bi bi-check-all me-1"></i>2/2 Suara Digunakan</span>
            <?php elseif ($vote_status['total_suara'] === 1): ?>
              <span class="badge bg-warning text-dark px-3 py-1"><i class="bi bi-hourglass-split me-1"></i>1/2 Suara Digunakan</span>
            <?php else: ?>
              <span class="badge bg-secondary px-3 py-1"><i class="bi bi-circle me-1"></i>0/2 Suara Digunakan</span>
            <?php endif; ?>
          </div>

          <div class="d-flex gap-2">
            <!-- Kakang Status -->
            <div class="flex-fill p-2 rounded-3 text-center <?= $vote_status['kakang'] ? 'bg-success bg-opacity-25 border border-success' : 'bg-dark bg-opacity-25 border border-secondary border-opacity-25' ?>">
              <small class="d-block text-light" style="font-size: 0.75rem;">Kategori Kakang</small>
              <?php if ($vote_status['kakang']): ?>
                <span class="text-success fw-bold small"><i class="bi bi-check-circle-fill me-1"></i>Sudah Memilih</span>
              <?php else: ?>
                <span class="text-warning fw-semibold small"><i class="bi bi-clock me-1"></i>Belum Memilih</span>
              <?php endif; ?>
            </div>

            <!-- Senduk Status -->
            <div class="flex-fill p-2 rounded-3 text-center <?= $vote_status['senduk'] ? 'bg-success bg-opacity-25 border border-success' : 'bg-dark bg-opacity-25 border border-secondary border-opacity-25' ?>">
              <small class="d-block text-light" style="font-size: 0.75rem;">Kategori Senduk</small>
              <?php if ($vote_status['senduk']): ?>
                <span class="text-success fw-bold small"><i class="bi bi-check-circle-fill me-1"></i>Sudah Memilih</span>
              <?php else: ?>
                <span class="text-warning fw-semibold small"><i class="bi bi-clock me-1"></i>Belum Memilih</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
  <?= display_flash() ?>

  <!-- Peringatan Jika Periode Tidak Aktif -->
  <?php if (!$active_periode || $active_periode['status'] !== 'aktif'): ?>
    <div class="alert alert-warning d-flex align-items-center mb-5 p-4 rounded-4 shadow-sm" role="alert">
      <i class="bi bi-exclamation-triangle-fill fs-2 me-3 text-warning"></i>
      <div>
        <h5 class="alert-heading font-serif mb-1">Periode Voting Sedang Ditutup</h5>
        <p class="mb-0 small">
          Saat ini proses pemberian suara belum dibuka oleh panitia pelaksana. Anda dapat meninjau profil seluruh kontestan pada menu <a href="<?= BASE_URL ?>pages/kontestan.php" class="alert-link">Katalog Kontestan</a>.
        </p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Tab Kategori Voting -->
  <ul class="nav nav-pills justify-content-center gap-3 mb-5" id="votingTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link rounded-pill px-4 py-2 fw-semibold fs-6 <?= ($active_tab === 'kakang') ? 'active bg-navy text-gold' : 'bg-white text-navy border' ?>" 
              id="kakang-tab" data-bs-toggle="pill" data-bs-target="#tab-kakang" type="button" role="tab" aria-controls="tab-kakang" aria-selected="<?= ($active_tab === 'kakang') ? 'true' : 'false' ?>">
        <i class="bi bi-gender-male me-2"></i> Bilik Kakang Ponorogo
        <?php if ($vote_status['kakang']): ?>
          <span class="badge bg-success ms-2"><i class="bi bi-check-circle"></i> Selesai</span>
        <?php endif; ?>
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link rounded-pill px-4 py-2 fw-semibold fs-6 <?= ($active_tab === 'senduk') ? 'active bg-navy text-gold' : 'bg-white text-navy border' ?>" 
              id="senduk-tab" data-bs-toggle="pill" data-bs-target="#tab-senduk" type="button" role="tab" aria-controls="tab-senduk" aria-selected="<?= ($active_tab === 'senduk') ? 'true' : 'false' ?>">
        <i class="bi bi-gender-female me-2"></i> Bilik Senduk Ponorogo
        <?php if ($vote_status['senduk']): ?>
          <span class="badge bg-success ms-2"><i class="bi bi-check-circle"></i> Selesai</span>
        <?php endif; ?>
      </button>
    </li>
  </ul>

  <!-- Tab Content -->
  <div class="tab-content" id="votingTabContent">

    <!-- 1. Bilik Kakang -->
    <div class="tab-pane fade <?= ($active_tab === 'kakang') ? 'show active' : '' ?>" id="tab-kakang" role="tabpanel" aria-labelledby="kakang-tab">
      <?php if ($vote_status['kakang']): ?>
        <div class="alert alert-success d-flex align-items-center mb-4 p-3 rounded-4 shadow-sm">
          <i class="bi bi-patch-check-fill fs-3 me-3 text-success"></i>
          <div>
            <strong>Anda telah menggunakan hak suara untuk Kategori Kakang.</strong>
            <p class="mb-0 small text-secondary">Terima kasih atas partisipasi Anda. Suara telah tersimpan aman dan tidak dapat diubah.</p>
          </div>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <?php if (!empty($kakang_list)): ?>
          <?php foreach ($kakang_list as $row): ?>
            <?php 
              $is_selected = ($vote_status['kakang_kontestan_id'] === (int)$row['id']);
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="contestant-card <?= $is_selected ? 'border-success shadow-lg' : '' ?>">
                <div class="contestant-img-wrapper">
                  <span class="contestant-badge-num"><?= escape($row['nomor_peserta']) ?></span>
                  <span class="contestant-badge-category badge-kakang">Kakang</span>
                  <img src="<?= get_kontestan_photo_url($row['foto'], 'Kakang') ?>" alt="<?= escape($row['nama']) ?>" class="contestant-img" loading="lazy">
                </div>
                <div class="contestant-info">
                  <h5 class="contestant-name"><?= escape($row['nama']) ?></h5>
                  <div class="contestant-instansi">
                    <i class="bi bi-building text-gold"></i>
                    <span class="text-truncate"><?= escape($row['asal_instansi']) ?></span>
                  </div>

                  <div class="contestant-footer mt-auto pt-3">
                    <?php if ($is_selected): ?>
                      <button class="btn btn-success btn-sm w-100" disabled>
                        <i class="bi bi-check2-circle me-1"></i> Pilihan Anda
                      </button>
                    <?php elseif ($vote_status['kakang']): ?>
                      <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                        Suara Digunakan
                      </button>
                    <?php elseif (!$active_periode || $active_periode['status'] !== 'aktif'): ?>
                      <button class="btn btn-secondary btn-sm w-100" disabled>
                        Voting Nonaktif
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-gold btn-sm w-100 btn-vote-trigger"
                              data-id="<?= $row['id'] ?>"
                              data-nama="<?= escape($row['nama']) ?>"
                              data-kategori="Kakang"
                              data-nomor="<?= escape($row['nomor_peserta']) ?>"
                              data-foto="<?= get_kontestan_photo_url($row['foto'], 'Kakang') ?>">
                        <i class="bi bi-check2-circle me-1"></i> Pilih Kakang Ini
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <p class="text-muted">Data kontestan Kakang belum tersedia.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- 2. Bilik Senduk -->
    <div class="tab-pane fade <?= ($active_tab === 'senduk') ? 'show active' : '' ?>" id="tab-senduk" role="tabpanel" aria-labelledby="senduk-tab">
      <?php if ($vote_status['senduk']): ?>
        <div class="alert alert-success d-flex align-items-center mb-4 p-3 rounded-4 shadow-sm">
          <i class="bi bi-patch-check-fill fs-3 me-3 text-success"></i>
          <div>
            <strong>Anda telah menggunakan hak suara untuk Kategori Senduk.</strong>
            <p class="mb-0 small text-secondary">Terima kasih atas partisipasi Anda. Suara telah tersimpan aman dan tidak dapat diubah.</p>
          </div>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <?php if (!empty($senduk_list)): ?>
          <?php foreach ($senduk_list as $row): ?>
            <?php 
              $is_selected = ($vote_status['senduk_kontestan_id'] === (int)$row['id']);
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="contestant-card <?= $is_selected ? 'border-success shadow-lg' : '' ?>">
                <div class="contestant-img-wrapper">
                  <span class="contestant-badge-num"><?= escape($row['nomor_peserta']) ?></span>
                  <span class="contestant-badge-category badge-senduk">Senduk</span>
                  <img src="<?= get_kontestan_photo_url($row['foto'], 'Senduk') ?>" alt="<?= escape($row['nama']) ?>" class="contestant-img" loading="lazy">
                </div>
                <div class="contestant-info">
                  <h5 class="contestant-name"><?= escape($row['nama']) ?></h5>
                  <div class="contestant-instansi">
                    <i class="bi bi-building text-gold"></i>
                    <span class="text-truncate"><?= escape($row['asal_instansi']) ?></span>
                  </div>

                  <div class="contestant-footer mt-auto pt-3">
                    <?php if ($is_selected): ?>
                      <button class="btn btn-success btn-sm w-100" disabled>
                        <i class="bi bi-check2-circle me-1"></i> Pilihan Anda
                      </button>
                    <?php elseif ($vote_status['senduk']): ?>
                      <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                        Suara Digunakan
                      </button>
                    <?php elseif (!$active_periode || $active_periode['status'] !== 'aktif'): ?>
                      <button class="btn btn-secondary btn-sm w-100" disabled>
                        Voting Nonaktif
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-gold btn-sm w-100 btn-vote-trigger"
                              data-id="<?= $row['id'] ?>"
                              data-nama="<?= escape($row['nama']) ?>"
                              data-kategori="Senduk"
                              data-nomor="<?= escape($row['nomor_peserta']) ?>"
                              data-foto="<?= get_kontestan_photo_url($row['foto'], 'Senduk') ?>">
                        <i class="bi bi-check2-circle me-1"></i> Pilih Senduk Ini
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <p class="text-muted">Data kontestan Senduk belum tersedia.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- Modal Konfirmasi Voting (Double Confirmation Modal) -->
<div class="modal fade" id="voteConfirmModal" tabindex="-1" aria-labelledby="voteConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
      <div class="modal-header bg-navy text-white border-bottom border-gold py-3">
        <h5 class="modal-title font-serif text-gold" id="voteConfirmModalLabel">
          <i class="bi bi-shield-check me-2"></i> Konfirmasi Pilihan Suara
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <div class="mb-3">
          <img id="modalContestantFoto" src="" alt="Foto Kontestan" class="rounded-circle object-fit-cover shadow-sm border border-3 border-gold" style="width: 110px; height: 110px;">
        </div>
        <span id="modalContestantNomor" class="badge bg-navy text-gold px-3 py-1 mb-2"></span>
        <h4 id="modalContestantNama" class="font-serif text-navy mb-1"></h4>
        <p id="modalContestantKategori" class="text-muted small mb-4"></p>

        <div class="alert alert-warning text-start small mb-0 rounded-3 border-0 bg-warning bg-opacity-10 text-dark">
          <i class="bi bi-info-circle-fill text-warning me-2 fs-5 float-start"></i>
          <div>
            <strong>Perhatian:</strong> Pilihan suara bersifat <strong>permanen dan final</strong>. Setelah dikonfirmasi, Anda tidak dapat mengubah atau membatalkan pilihan ini.
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
          Batal / Kembali
        </button>
        <form id="formVote" action="<?= BASE_URL ?>proses/simpan-voting.php" method="POST" class="m-0">
          <?= csrf_input() ?>
          <input type="hidden" name="kontestan_id" id="inputKontestanId" value="">
          <input type="hidden" name="kategori" id="inputKategori" value="">
          <button type="submit" id="btnSubmitVote" class="btn btn-gold px-4">
            <i class="bi bi-check-circle-fill me-1"></i> Ya, Konfirmasi Suara
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php 
$extra_js = '<script src="' . BASE_URL . 'assets/js/voting.js"></script>';
require_once __DIR__ . '/../includes/footer.php'; 
?>

