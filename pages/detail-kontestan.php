<?php
/**
 * Halaman Detail Profil Kontestan
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$kontestan = null;

if ($pdo && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM kontestan WHERE id = ? AND status = 'aktif' LIMIT 1");
        $stmt->execute([$id]);
        $kontestan = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in detail-kontestan.php: " . $e->getMessage());
    }
}

// Jika kontestan tidak ditemukan, tampilkan 404
if (!$kontestan) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$page_title = $kontestan['nomor_peserta'] . ' — ' . $kontestan['nama'];
$active_nav = 'kontestan';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Format tanggal lahir
$tgl_lahir = !empty($kontestan['tanggal_lahir']) ? format_tanggal_indo($kontestan['tanggal_lahir'], false) : null;
$ttl = [];
if (!empty($kontestan['tempat_lahir'])) $ttl[] = $kontestan['tempat_lahir'];
if ($tgl_lahir) $ttl[] = $tgl_lahir;
$ttl_text = !empty($ttl) ? implode(', ', $ttl) : 'Ponorogo, Jawa Timur';
?>

<!-- Breadcrumb Header -->
<div class="py-4 bg-navy text-white">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-gold text-decoration-none">Beranda</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>pages/kontestan.php" class="text-gold text-decoration-none">Kontestan</a></li>
        <li class="breadcrumb-item active text-light" aria-current="page"><?= escape($kontestan['nomor_peserta']) ?></li>
      </ol>
    </nav>
    <h2 class="font-serif text-white mb-0"><?= escape($kontestan['nama']) ?></h2>
  </div>
</div>

<div class="container py-5">
  <div class="row g-5">
    <!-- Kolom Kiri: Foto Resmi & Kartu Aksi -->
    <div class="col-lg-5">
      <div class="sticky-top contestant-sticky-wrapper" style="top: 96px; z-index: 10;">
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden bg-white mb-4">
          <div class="position-relative">
            <span class="contestant-badge-num" style="top: 20px; left: 20px; font-size: 1rem; padding: 6px 16px;">
              <?= escape($kontestan['nomor_peserta']) ?>
            </span>
            <span class="contestant-badge-category <?= ($kontestan['kategori'] === 'Kakang') ? 'badge-kakang' : 'badge-senduk' ?>" style="top: 20px; right: 20px;">
              Kategori <?= escape($kontestan['kategori']) ?>
            </span>
            <img src="<?= get_kontestan_photo_url($kontestan['foto'], $kontestan['kategori']) ?>" alt="<?= escape($kontestan['nama']) ?>" class="w-100 object-fit-cover" style="aspect-ratio: 3/4;">
          </div>
          <div class="p-4 text-center">
            <h4 class="font-serif text-navy mb-1"><?= escape($kontestan['nama']) ?></h4>
            <div class="text-muted small mb-3">
              <i class="bi bi-building text-gold me-1"></i> <?= escape($kontestan['asal_instansi']) ?>
            </div>

            <div class="d-grid gap-2">
              <a href="<?= BASE_URL ?>pages/voting.php?pilih=<?= $kontestan['id'] ?>&kat=<?= $kontestan['kategori'] ?>" class="btn btn-gold btn-lg">
                <i class="bi bi-check2-circle me-2"></i> Berikan Suara untuk <?= escape($kontestan['nomor_peserta']) ?>
              </a>
              <a href="<?= BASE_URL ?>pages/kontestan.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar Kontestan
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan: Detail Biodata, Visi, Misi, Prestasi -->
    <div class="col-lg-7">
      <!-- Biodata Ringkas -->
      <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
        <h5 class="font-serif text-navy border-bottom pb-2 mb-3">
          <i class="bi bi-person-badge text-gold me-2"></i> Informasi Biodata
        </h5>
        <div class="row g-3">
          <div class="col-sm-6">
            <small class="text-muted d-block">Nomor Peserta</small>
            <span class="fw-bold text-navy"><?= escape($kontestan['nomor_peserta']) ?></span>
          </div>
          <div class="col-sm-6">
            <small class="text-muted d-block">Kategori Pemilihan</small>
            <span class="fw-bold text-navy"><?= escape($kontestan['kategori']) ?> Ponorogo</span>
          </div>
          <div class="col-sm-6">
            <small class="text-muted d-block">Tempat, Tanggal Lahir</small>
            <span class="fw-semibold text-dark"><?= escape($ttl_text) ?></span>
          </div>
          <div class="col-sm-6">
            <small class="text-muted d-block">Asal Sekolah / Instansi</small>
            <span class="fw-semibold text-dark"><?= escape($kontestan['asal_instansi']) ?></span>
          </div>
        </div>
      </div>

      <!-- Deskripsi Profil -->
      <?php if (!empty($kontestan['deskripsi'])): ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
          <h5 class="font-serif text-navy border-bottom pb-2 mb-3">
            <i class="bi bi-card-text text-gold me-2"></i> Profil Singkat
          </h5>
          <p class="text-secondary leading-relaxed mb-0">
            <?= nl2br(escape($kontestan['deskripsi'])) ?>
          </p>
        </div>
      <?php endif; ?>

      <!-- Visi & Misi -->
      <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
        <h5 class="font-serif text-navy border-bottom pb-2 mb-3">
          <i class="bi bi-compass text-gold me-2"></i> Visi & Misi Duta Wisata
        </h5>

        <div class="mb-4">
          <h6 class="fw-bold text-navy mb-2"><i class="bi bi-bullseye text-gold me-2"></i>Visi:</h6>
          <div class="p-3 rounded-3" style="background: #F8FAFC; border-left: 4px solid var(--accent-gold);">
            <p class="mb-0 text-dark italic">"<?= escape($kontestan['visi'] ?: 'Berbakti melestarikan kebudayaan dan memajukan sektor pariwisata Ponorogo.') ?>"</p>
          </div>
        </div>

        <div>
          <h6 class="fw-bold text-navy mb-2"><i class="bi bi-list-check text-gold me-2"></i>Misi:</h6>
          <div class="text-secondary leading-relaxed ps-2">
            <?= nl2br(escape($kontestan['misi'] ?: 'Mempromosikan potensi wisata Ponorogo melalui aksi nyata, karya kreatif, dan kolaborasi pemuda.')) ?>
          </div>
        </div>
      </div>

      <!-- Prestasi -->
      <?php if (!empty($kontestan['prestasi'])): ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
          <h5 class="font-serif text-navy border-bottom pb-2 mb-3">
            <i class="bi bi-trophy text-gold me-2"></i> Rekam Jejak & Prestasi
          </h5>
          <div class="text-secondary leading-relaxed">
            <?= nl2br(escape($kontestan['prestasi'])) ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Tombol Aksi Bawah -->
      <div class="card border-0 rounded-4 p-4 text-center" style="background: linear-gradient(135deg, #101B35, #243F73); color: white;">
        <h4 class="font-serif text-gold mb-2">Dukung <?= escape($kontestan['nama']) ?>!</h4>
        <p class="small text-light opacity-75 mb-3">Setiap suara yang Anda berikan sangat berarti dalam menentukan Duta Wisata Favorit Ponorogo.</p>
        <div>
          <a href="<?= BASE_URL ?>pages/voting.php?pilih=<?= $kontestan['id'] ?>&kat=<?= $kontestan['kategori'] ?>" class="btn btn-gold btn-lg px-4">
            <i class="bi bi-check2-circle me-2"></i> Vote Sekarang
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

