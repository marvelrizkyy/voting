<?php
/**
 * Halaman Beranda Utama (Landing Page)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Beranda';
$active_nav = 'beranda';

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Ambil periode voting aktif
$active_periode = get_active_periode($pdo);

// Ambil kontestan unggulan (3 Kakang dan 3 Senduk)
$featured_contestants = [];
$top_kakang = [];
$top_senduk = [];
$total_suara_kakang = 0;
$total_suara_senduk = 0;

if ($pdo) {
    try {
        // Kontestan Featured (Acak terstruktur untuk netralitas)
        $stmtFeatured = $pdo->query("
            (SELECT * FROM kontestan WHERE kategori = 'Kakang' AND status = 'aktif' ORDER BY id ASC LIMIT 3)
            UNION ALL
            (SELECT * FROM kontestan WHERE kategori = 'Senduk' AND status = 'aktif' ORDER BY id ASC LIMIT 3)
        ");
        $featured_contestants = $stmtFeatured->fetchAll();

        if ($active_periode) {
            $p_id = (int)$active_periode['id'];

            // Total suara per kategori
            $stmtTotK = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Kakang'");
            $stmtTotK->execute([$p_id]);
            $total_suara_kakang = (int)$stmtTotK->fetchColumn();

            $stmtTotS = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Senduk'");
            $stmtTotS->execute([$p_id]);
            $total_suara_senduk = (int)$stmtTotS->fetchColumn();

            // Preview Klasemen Kakang (Top 3)
            $stmtK = $pdo->prepare("
                SELECT k.id, k.nomor_peserta, k.nama, k.asal_instansi, k.foto, COUNT(v.id) AS total_suara
                FROM kontestan k
                LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
                WHERE k.kategori = 'Kakang' AND k.status = 'aktif'
                GROUP BY k.id
                ORDER BY total_suara DESC, k.nomor_peserta ASC
                LIMIT 3
            ");
            $stmtK->execute([':p_id' => $p_id]);
            $top_kakang = $stmtK->fetchAll();

            // Preview Klasemen Senduk (Top 3)
            $stmtS = $pdo->prepare("
                SELECT k.id, k.nomor_peserta, k.nama, k.asal_instansi, k.foto, COUNT(v.id) AS total_suara
                FROM kontestan k
                LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
                WHERE k.kategori = 'Senduk' AND k.status = 'aktif'
                GROUP BY k.id
                ORDER BY total_suara DESC, k.nomor_peserta ASC
                LIMIT 3
            ");
            $stmtS->execute([':p_id' => $p_id]);
            $top_senduk = $stmtS->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error fetching home data: " . $e->getMessage());
    }
}
?>

<!-- 1. Hero Section -->
<section class="hero-section">
  <div class="hero-overlay"></div>
  <div class="container position-relative z-2">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(214, 174, 88, 0.15); border: 1px solid rgba(214, 174, 88, 0.35);">
          <i class="bi bi-stars text-gold"></i>
          <span class="small text-gold fw-semibold text-uppercase tracking-wider">Pemilihan Duta Wisata Daerah Ponorogo</span>
        </div>
        <h1 class="hero-title">
          Bumi Reog Menanti <span>Kakang & Senduk 2026</span>
        </h1>
        <p class="hero-lead">
          Wadah apresiasi dan partisipasi masyarakat Ponorogo dalam menentukan figur pemuda-pemudi inspiratif, berwawasan budaya luhur, dan berdaya saing global untuk memajukan pariwisata daerah.
        </p>

        <div class="d-flex flex-wrap gap-3 mt-4">
          <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-gold btn-lg px-4 shadow">
            <i class="bi bi-check2-circle me-2"></i> Berikan Suara Sekarang
          </a>
          <a href="<?= BASE_URL ?>pages/kontestan.php" class="btn btn-outline-gold btn-lg px-4">
            <i class="bi bi-people me-2"></i> Jelajahi Kontestan
          </a>
        </div>

        <!-- Periode Info & Status -->
        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex flex-wrap align-items-center gap-4 text-light small">
          <div>
            <i class="bi bi-calendar3 text-gold me-1"></i>
            <strong>Periode:</strong> 
            <?= $active_periode ? format_tanggal_indo($active_periode['tanggal_mulai'], false) . ' — ' . format_tanggal_indo($active_periode['tanggal_selesai'], false) : 'Belum Ditetapkan' ?>
          </div>
          <div>
            <i class="bi bi-shield-check text-gold me-1"></i>
            <strong>Status:</strong>
            <?php if ($active_periode && $active_periode['status'] === 'aktif'): ?>
              <span class="badge bg-success text-white">Voting Dibuka</span>
            <?php else: ?>
              <span class="badge bg-danger text-white">Voting Ditutup</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Countdown Widget -->
      <div class="col-lg-5">
        <div class="countdown-container">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-serif text-gold mb-0"><i class="bi bi-hourglass-split me-2"></i>Hitung Mundur Voting</h5>
            <span id="cdStatusBadge" class="badge <?= ($active_periode && $active_periode['status'] === 'aktif') ? 'bg-success' : 'bg-secondary' ?> px-2 py-1">
              <?= ($active_periode && $active_periode['status'] === 'aktif') ? 'Sedang Berlangsung' : 'Nonaktif' ?>
            </span>
          </div>
          <p class="small text-secondary mb-3">
            Gunakan hak suara Anda sebelum periode voting berakhir. Satu suara Anda menentukan masa depan pariwisata Ponorogo!
          </p>

          <?php 
            $target_timestamp = $active_periode ? date('Y-m-d\TH:i:s', strtotime($active_periode['tanggal_selesai'])) : date('Y-m-d\TH:i:s', strtotime('+30 days'));
          ?>
          <div class="d-flex justify-content-between gap-2" id="votingCountdown" data-target-date="<?= $target_timestamp ?>">
            <div class="countdown-box flex-fill">
              <div class="countdown-val" id="cdDays">00</div>
              <div class="countdown-lbl">Hari</div>
            </div>
            <div class="countdown-box flex-fill">
              <div class="countdown-val" id="cdHours">00</div>
              <div class="countdown-lbl">Jam</div>
            </div>
            <div class="countdown-box flex-fill">
              <div class="countdown-val" id="cdMinutes">00</div>
              <div class="countdown-lbl">Menit</div>
            </div>
            <div class="countdown-box flex-fill">
              <div class="countdown-val" id="cdSeconds">00</div>
              <div class="countdown-lbl">Detik</div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-top border-light border-opacity-10 text-center">
            <a href="<?= BASE_URL ?>pages/klasemen.php" class="text-gold text-decoration-none small fw-semibold">
              Lihat Perolehan Suara Sementara <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 2. Section Tentang Kegiatan -->
<section class="py-5" style="background-color: var(--white);">
  <div class="container py-4">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="position-relative">
          <div class="p-4 rounded-4" style="background: linear-gradient(135deg, #101B35, #243F73); color: white;">
            <div class="d-flex align-items-center gap-3 mb-4">
              <div class="bg-gold p-3 rounded-circle text-navy fs-3">
                <i class="bi bi-award-fill"></i>
              </div>
              <div>
                <h4 class="font-serif text-gold mb-0">Duta Wisata Ponorogo</h4>
                <small class="text-light opacity-75">Bumi Reog Warisan Budaya Luhur</small>
              </div>
            </div>
            <p class="text-light opacity-90 mb-4">
              Ajang Pemilihan Kakang Senduk diselenggarakan secara berkala oleh Pemerintah Kabupaten Ponorogo melalui Dinas Kebudayaan, Pariwisata, Pemuda dan Olahraga untuk memilih figur pemuda dan pemudi terbaik yang akan mengemban amanah sebagai duta promosi pariwisata, kebudayaan, dan ekonomi kreatif daerah.
            </p>
            <div class="row g-3 text-center">
              <div class="col-4">
                <div class="p-2 rounded bg-white bg-opacity-10">
                  <div class="fs-4 fw-bold text-gold">16</div>
                  <small class="text-light" style="font-size: 0.75rem;">Finalis Pilihan</small>
                </div>
              </div>
              <div class="col-4">
                <div class="p-2 rounded bg-white bg-opacity-10">
                  <div class="fs-4 fw-bold text-gold">2</div>
                  <small class="text-light" style="font-size: 0.75rem;">Kategori Duta</small>
                </div>
              </div>
              <div class="col-4">
                <div class="p-2 rounded bg-white bg-opacity-10">
                  <div class="fs-4 fw-bold text-gold">100%</div>
                  <small class="text-light" style="font-size: 0.75rem;">Transparan</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <span class="text-gold fw-bold text-uppercase small tracking-wider">Peran & Filosofi</span>
        <h2 class="font-serif text-navy mt-1 mb-3">Membangun Kejayaan Wisata dari Semangat Budaya</h2>
        <p class="text-secondary mb-4">
          Duta Wisata bukan sekadar gelar panggung, melainkan tanggung jawab diplomasi budaya untuk membawa kekayaan seni Reog Ponorogo, keelokan Telaga Ngebel, serta kearifan tradisi lokal ke kancah nasional hingga internasional.
        </p>

        <div class="d-flex flex-column gap-3">
          <div class="d-flex gap-3">
            <div class="fs-4 text-gold"><i class="bi bi-shield-check"></i></div>
            <div>
              <h6 class="fw-bold text-navy mb-1">Integritas & Kepribadian Luhur</h6>
              <p class="small text-secondary mb-0">Meneladani sifat kesatria Warok dan keanggunan putri Songgolangit dalam bersikap dan bertutur kata.</p>
            </div>
          </div>
          <div class="d-flex gap-3">
            <div class="fs-4 text-gold"><i class="bi bi-megaphone"></i></div>
            <div>
              <h6 class="fw-bold text-navy mb-1">Kecakapan Komunikasi & Promosi Digital</h6>
              <p class="small text-secondary mb-0">Menguasai komunikasi bilingual dan pemanfaatan media digital untuk menyuarakan pesona Ponorogo kepada dunia.</p>
            </div>
          </div>
          <div class="d-flex gap-3">
            <div class="fs-4 text-gold"><i class="bi bi-lightbulb"></i></div>
            <div>
              <h6 class="fw-bold text-navy mb-1">Inovasi Ekonomi Kreatif</h6>
              <p class="small text-secondary mb-0">Menggerakkan UMKM, sentra kerajinan kriya Reog, dan kuliner khas agar berdaya saing mandiri.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 3. Section Kategori Kakang & Senduk -->
<section class="py-5" style="background-color: var(--bg-cream);">
  <div class="container py-3">
    <div class="text-center max-w-700 mx-auto mb-5">
      <span class="text-gold fw-bold text-uppercase small tracking-wider">Dua Representasi Luhur</span>
      <h2 class="font-serif text-navy mt-1">Kategori Pemilihan Duta Wisata</h2>
      <p class="text-secondary small">Setiap pemilih terdaftar memiliki kesempatan memberikan tepat satu suara pada masing-masing kategori.</p>
    </div>

    <div class="row g-4">
      <!-- Kartu Kakang -->
      <div class="col-md-6">
        <div class="card border-0 rounded-4 shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #101B35, #1E2E54); color: white; border-top: 4px solid #D6AE58 !important;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge badge-kakang px-3 py-2">Kategori Pria</span>
            <i class="bi bi-gender-male fs-2 text-gold"></i>
          </div>
          <h3 class="font-serif text-gold mb-2">Kakang Ponorogo</h3>
          <p class="text-light opacity-90 small mb-4">
            Gelar kehormatan bagi pemuda Ponorogo yang memiliki ketegasan budi pekerti, wawasan kebudayaan luas, kharisma kepemimpinan luhur, dan komitmen memajukan pariwisata daerah.
          </p>
          <ul class="list-unstyled small text-light opacity-75 mb-4">
            <li class="mb-2"><i class="bi bi-check-circle text-gold me-2"></i> Berusia 17 – 24 tahun berdomisili atau asal instansi Ponorogo</li>
            <li class="mb-2"><i class="bi bi-check-circle text-gold me-2"></i> Menguasai wawasan sejarah Babad Ponorogo & Seni Reog</li>
            <li><i class="bi bi-check-circle text-gold me-2"></i> Memiliki kecakapan komunikasi publik & bahasa asing</li>
          </ul>
          <div class="mt-auto pt-3 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
            <span class="text-gold small fw-semibold">Pilih Kakang Favorit</span>
            <a href="<?= BASE_URL ?>pages/voting.php?tab=kakang" class="btn btn-outline-gold btn-sm px-3">
              Buka Bilik Kakang <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Kartu Senduk -->
      <div class="col-md-6">
        <div class="card border-0 rounded-4 shadow-sm p-4 h-100" style="background: linear-gradient(135deg, #220B24, #441444); color: white; border-top: 4px solid #E572B1 !important;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge badge-senduk px-3 py-2">Kategori Wanita</span>
            <i class="bi bi-gender-female fs-2 text-pink" style="color: #F8B4D9;"></i>
          </div>
          <h3 class="font-serif text-gold mb-2">Senduk Ponorogo</h3>
          <p class="text-light opacity-90 small mb-4">
            Gelar prestisius bagi pemudi Ponorogo yang memancarkan keanggunan putri Jawa, kecerdasan intelegensi, kepedulian sosial, serta kemampuan mempromosikan pesona keindahan Bumi Reog.
          </p>
          <ul class="list-unstyled small text-light opacity-75 mb-4">
            <li class="mb-2"><i class="bi bi-check-circle text-gold me-2"></i> Berusia 17 – 24 tahun berdomisili atau asal instansi Ponorogo</li>
            <li class="mb-2"><i class="bi bi-check-circle text-gold me-2"></i> Memahami seni kriya, batik Ponoragan, dan busana adat</li>
            <li><i class="bi bi-check-circle text-gold me-2"></i> Berpikir kritis, ramah, dan adaptif di era digital</li>
          </ul>
          <div class="mt-auto pt-3 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
            <span class="text-gold small fw-semibold">Pilih Senduk Favorit</span>
            <a href="<?= BASE_URL ?>pages/voting.php?tab=senduk" class="btn btn-outline-gold btn-sm px-3">
              Buka Bilik Senduk <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 4. Section Kontestan Unggulan (Featured) -->
<section class="py-5" style="background-color: var(--white);">
  <div class="container py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
      <div>
        <span class="text-gold fw-bold text-uppercase small tracking-wider">Profil Finalis</span>
        <h2 class="font-serif text-navy mt-1 mb-0">Kontestan Kakang & Senduk</h2>
        <small class="text-muted d-block mt-1">Daftar kontestan disajikan secara berimbang untuk menjaga netralitas pemilihan.</small>
      </div>
      <a href="<?= BASE_URL ?>pages/kontestan.php" class="btn btn-outline-gold btn-sm mt-3 mt-md-0">
        Lihat Semua Kontestan <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php if (!empty($featured_contestants)): ?>
        <?php foreach ($featured_contestants as $k): ?>
          <div class="col-lg-4 col-md-6">
            <div class="contestant-card">
              <div class="contestant-img-wrapper">
                <span class="contestant-badge-num"><?= escape($k['nomor_peserta']) ?></span>
                <span class="contestant-badge-category <?= ($k['kategori'] === 'Kakang') ? 'badge-kakang' : 'badge-senduk' ?>">
                  <?= escape($k['kategori']) ?>
                </span>
                <img src="<?= get_kontestan_photo_url($k['foto'], $k['kategori']) ?>" alt="<?= escape($k['nama']) ?>" class="contestant-img" loading="lazy">
              </div>
              <div class="contestant-info">
                <h5 class="contestant-name"><?= escape($k['nama']) ?></h5>
                <div class="contestant-instansi">
                  <i class="bi bi-building text-gold"></i>
                  <span><?= escape($k['asal_instansi']) ?></span>
                </div>
                <p class="small text-secondary mb-3 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  <?= escape($k['visi'] ?: $k['deskripsi']) ?>
                </p>
                <div class="contestant-footer">
                  <a href="<?= BASE_URL ?>pages/detail-kontestan.php?id=<?= $k['id'] ?>" class="btn btn-outline-secondary btn-sm flex-fill">
                    <i class="bi bi-eye me-1"></i> Profil
                  </a>
                  <a href="<?= BASE_URL ?>pages/voting.php?pilih=<?= $k['id'] ?>&kat=<?= $k['kategori'] ?>" class="btn btn-gold btn-sm flex-fill">
                    <i class="bi bi-check2-circle me-1"></i> Vote
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <p class="text-muted">Data kontestan belum tersedia dalam database.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- 5. Section Preview Klasemen Sementara -->
<section class="py-5" style="background-color: var(--bg-cream);">
  <div class="container py-3">
    <div class="text-center max-w-700 mx-auto mb-5">
      <span class="text-gold fw-bold text-uppercase small tracking-wider">Perolehan Suara Nyata</span>
      <h2 class="font-serif text-navy mt-1">Klasemen Sementara</h2>
      <p class="text-secondary small">Data diperbarui secara langsung dari database voting. Suara sah dihitung otomatis tanpa manipulasi.</p>
    </div>

    <div class="row g-4">
      <!-- Preview Klasemen Kakang -->
      <div class="col-lg-6">
        <div class="card border-0 rounded-4 shadow-sm p-4 h-100 bg-white">
          <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
            <h5 class="font-serif text-navy mb-0">
              <i class="bi bi-trophy-fill text-gold me-2"></i> Top 3 Kakang
            </h5>
            <span class="badge bg-navy text-gold">Total: <?= number_format($total_suara_kakang, 0, ',', '.') ?> Suara</span>
          </div>

          <?php if (!empty($top_kakang)): ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($top_kakang as $idx => $row): ?>
                <?php 
                  $pct = $total_suara_kakang > 0 ? round(($row['total_suara'] / $total_suara_kakang) * 100, 1) : 0;
                  $rank = $idx + 1;
                ?>
                <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background: #F8FAFC;">
                  <div class="fw-bold font-serif fs-4 text-center" style="width: 32px; color: <?= $rank === 1 ? '#D6AE58' : '#64748B' ?>;">
                    #<?= $rank ?>
                  </div>
                  <img src="<?= get_kontestan_photo_url($row['foto'], 'Kakang') ?>" alt="<?= escape($row['nama']) ?>" class="rounded-circle object-fit-cover" style="width: 48px; height: 48px; border: 2px solid <?= $rank === 1 ? '#D6AE58' : '#CBD5E1' ?>;">
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-bold text-truncate text-navy"><?= escape($row['nama']) ?></div>
                    <small class="text-muted d-block text-truncate"><?= escape($row['nomor_peserta']) ?> • <?= escape($row['asal_instansi']) ?></small>
                  </div>
                  <div class="text-end">
                    <span class="fw-bold text-navy"><?= number_format($row['total_suara'], 0, ',', '.') ?></span>
                    <small class="text-muted d-block"><?= $pct ?>%</small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted small text-center py-4 mb-0">Belum ada suara masuk untuk Kakang.</p>
          <?php endif; ?>

          <div class="mt-4 pt-3 border-top text-center">
            <a href="<?= BASE_URL ?>pages/klasemen.php?tab=kakang" class="text-navy text-decoration-none small fw-semibold">
              Lihat Klasemen Lengkap Kakang <i class="bi bi-chevron-right ms-1 text-gold"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Preview Klasemen Senduk -->
      <div class="col-lg-6">
        <div class="card border-0 rounded-4 shadow-sm p-4 h-100 bg-white">
          <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
            <h5 class="font-serif text-navy mb-0">
              <i class="bi bi-trophy-fill text-pink me-2" style="color: #E572B1;"></i> Top 3 Senduk
            </h5>
            <span class="badge bg-navy text-gold">Total: <?= number_format($total_suara_senduk, 0, ',', '.') ?> Suara</span>
          </div>

          <?php if (!empty($top_senduk)): ?>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($top_senduk as $idx => $row): ?>
                <?php 
                  $pct = $total_suara_senduk > 0 ? round(($row['total_suara'] / $total_suara_senduk) * 100, 1) : 0;
                  $rank = $idx + 1;
                ?>
                <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background: #F8FAFC;">
                  <div class="fw-bold font-serif fs-4 text-center" style="width: 32px; color: <?= $rank === 1 ? '#D6AE58' : '#64748B' ?>;">
                    #<?= $rank ?>
                  </div>
                  <img src="<?= get_kontestan_photo_url($row['foto'], 'Senduk') ?>" alt="<?= escape($row['nama']) ?>" class="rounded-circle object-fit-cover" style="width: 48px; height: 48px; border: 2px solid <?= $rank === 1 ? '#D6AE58' : '#CBD5E1' ?>;">
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-bold text-truncate text-navy"><?= escape($row['nama']) ?></div>
                    <small class="text-muted d-block text-truncate"><?= escape($row['nomor_peserta']) ?> • <?= escape($row['asal_instansi']) ?></small>
                  </div>
                  <div class="text-end">
                    <span class="fw-bold text-navy"><?= number_format($row['total_suara'], 0, ',', '.') ?></span>
                    <small class="text-muted d-block"><?= $pct ?>%</small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted small text-center py-4 mb-0">Belum ada suara masuk untuk Senduk.</p>
          <?php endif; ?>

          <div class="mt-4 pt-3 border-top text-center">
            <a href="<?= BASE_URL ?>pages/klasemen.php?tab=senduk" class="text-navy text-decoration-none small fw-semibold">
              Lihat Klasemen Lengkap Senduk <i class="bi bi-chevron-right ms-1 text-gold"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

