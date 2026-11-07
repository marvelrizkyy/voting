<?php
/**
 * Tampilan Laporan Resmi Siap Cetak / Simpan PDF
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

$periode_id = isset($_GET['periode_id']) ? (int)$_GET['periode_id'] : 0;
if ($periode_id <= 0) {
    $active = get_active_periode($pdo);
    $periode_id = $active ? (int)$active['id'] : 1;
}

if (!$pdo) {
    die("Database tidak terhubung.");
}

$stmtP = $pdo->prepare("SELECT * FROM periode_voting WHERE id = ? LIMIT 1");
$stmtP->execute([$periode_id]);
$periode = $stmtP->fetch();

if (!$periode) {
    die("Periode tidak ditemukan.");
}

// Total Suara
$stmtTotK = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Kakang'");
$stmtTotK->execute([$periode_id]);
$total_kakang = (int)$stmtTotK->fetchColumn();

$stmtTotS = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Senduk'");
$stmtTotS->execute([$periode_id]);
$total_senduk = (int)$stmtTotS->fetchColumn();

// Data Kakang
$stmtKakang = $pdo->prepare("
    SELECT 
        k.nomor_peserta, k.nama, k.asal_instansi,
        COUNT(v.id) AS total_suara,
        ROUND((COUNT(v.id) / NULLIF(:tot_k, 0)) * 100, 2) AS persentase
    FROM kontestan k
    LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
    WHERE k.kategori = 'Kakang' AND k.status = 'aktif'
    GROUP BY k.id
    ORDER BY total_suara DESC, k.nomor_peserta ASC
");
$stmtKakang->execute([':tot_k' => $total_kakang, ':p_id' => $periode_id]);
$list_kakang = $stmtKakang->fetchAll();

// Data Senduk
$stmtSenduk = $pdo->prepare("
    SELECT 
        k.nomor_peserta, k.nama, k.asal_instansi,
        COUNT(v.id) AS total_suara,
        ROUND((COUNT(v.id) / NULLIF(:tot_s, 0)) * 100, 2) AS persentase
    FROM kontestan k
    LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
    WHERE k.kategori = 'Senduk' AND k.status = 'aktif'
    GROUP BY k.id
    ORDER BY total_suara DESC, k.nomor_peserta ASC
");
$stmtSenduk->execute([':tot_s' => $total_senduk, ':p_id' => $periode_id]);
$list_senduk = $stmtSenduk->fetchAll();

// Log aktivitas
log_activity(
    $pdo,
    $_SESSION['user_id'],
    "Membuka lembar cetak dokumen resmi hasil voting periode '{$periode['nama_periode']}'",
    'Laporan'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Hasil Voting — <?= escape($periode['nama_periode']) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/logo.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Times New Roman', Times, serif;
      background-color: #F8F9FA;
      color: #000000;
      line-height: 1.4;
    }
    .print-sheet {
      background: #FFFFFF;
      max-width: 900px;
      margin: 30px auto;
      padding: 40px 50px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .kop-header {
      border-bottom: 3px double #000000;
      padding-bottom: 15px;
      margin-bottom: 24px;
      text-align: center;
    }
    .table-print {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
      font-size: 0.95rem;
    }
    .table-print th, .table-print td {
      border: 1px solid #000000;
      padding: 7px 10px;
    }
    .table-print th {
      background-color: #F0F0F0;
      text-align: center;
      font-weight: bold;
    }
    @media print {
      body {
        background-color: #FFFFFF !important;
      }
      .print-sheet {
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

<!-- Floating Action Bar (Hanya tampil di layar, tersembunyi saat cetak) -->
<div class="no-print bg-dark py-2 px-4 sticky-top text-white d-flex justify-content-between align-items-center shadow">
  <div>
    <strong>Dokumen Resmi Berita Acara Rekapitulasi Suara</strong>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= BASE_URL ?>admin/laporan.php" class="btn btn-outline-light btn-sm">
      Kembali ke Panel Admin
    </a>
    <button type="button" class="btn btn-warning btn-sm fw-bold px-3" onclick="window.print();">
      🖨️ Cetak / Simpan ke PDF
    </button>
  </div>
</div>

<div class="print-sheet">
  <!-- Kop Surat Resmi -->
  <div class="kop-header">
    <h5 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 1px;">PEMERINTAH KABUPATEN PONOROGO</h5>
    <h4 class="mb-0 fw-bold text-uppercase">DINAS KEBUDAYAAN, PARIWISATA, PEMUDA DAN OLAHRAGA</h4>
    <small style="font-size: 0.85rem;">Jl. Pramuka No. 1 Ponorogo, Jawa Timur • Telp: (0352) 481234 • Email: pariwisata@ponorogo.go.id</small>
  </div>

  <!-- Judul Dokumen -->
  <div class="text-center mb-4">
    <h5 class="fw-bold mb-1 text-uppercase text-decoration-underline">BERITA ACARA REKAPITULASI HASIL VOTING</h5>
    <h6 class="fw-bold text-uppercase mb-1"><?= escape($periode['nama_periode']) ?></h6>
    <small>Nomor: 045.2 / DWP / <?= date('Y') ?></small>
  </div>

  <p style="text-align: justify; text-indent: 30px;" class="mb-3">
    Pada hari ini, <strong><?= date('l') ?></strong> tanggal <strong><?= format_tanggal_indo(date('Y-m-d'), false) ?></strong>, Panitia Penyelenggara Pemilihan Duta Wisata Daerah Kabupaten Ponorogo menyatakan bahwa proses pemungutan suara digital masyarakat telah sah, transparan, dan terverifikasi oleh sistem dengan rincian hasil sebagai berikut:
  </p>

  <!-- 1. TABEL KAKANG -->
  <h6 class="fw-bold mb-2">A. Hasil Perolehan Suara Kategori Kakang Ponorogo (Total: <?= number_format($total_kakang, 0, ',', '.') ?> Suara)</h6>
  <table class="table-print">
    <thead>
      <tr>
        <th style="width: 50px;">Rank</th>
        <th style="width: 80px;">No. Peserta</th>
        <th>Nama Lengkap</th>
        <th>Asal Instansi</th>
        <th style="width: 100px;">Suara Sah</th>
        <th style="width: 90px;">Persentase</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($list_kakang)): ?>
        <?php foreach ($list_kakang as $idx => $r): ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td class="text-center fw-bold"><?= escape($r['nomor_peserta']) ?></td>
            <td class="fw-bold"><?= escape($r['nama']) ?></td>
            <td><?= escape($r['asal_instansi']) ?></td>
            <td class="text-end fw-bold"><?= number_format($r['total_suara'], 0, ',', '.') ?></td>
            <td class="text-end"><?= $r['persentase'] ?: '0.00' ?>%</td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">Belum ada data.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- 2. TABEL SENDUK -->
  <h6 class="fw-bold mb-2 mt-4">B. Hasil Perolehan Suara Kategori Senduk Ponorogo (Total: <?= number_format($total_senduk, 0, ',', '.') ?> Suara)</h6>
  <table class="table-print">
    <thead>
      <tr>
        <th style="width: 50px;">Rank</th>
        <th style="width: 80px;">No. Peserta</th>
        <th>Nama Lengkap</th>
        <th>Asal Instansi</th>
        <th style="width: 100px;">Suara Sah</th>
        <th style="width: 90px;">Persentase</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($list_senduk)): ?>
        <?php foreach ($list_senduk as $idx => $r): ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td class="text-center fw-bold"><?= escape($r['nomor_peserta']) ?></td>
            <td class="fw-bold"><?= escape($r['nama']) ?></td>
            <td><?= escape($r['asal_instansi']) ?></td>
            <td class="text-end fw-bold"><?= number_format($r['total_suara'], 0, ',', '.') ?></td>
            <td class="text-end"><?= $r['persentase'] ?: '0.00' ?>%</td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">Belum ada data.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <p style="text-align: justify;" class="mt-4">
    Demikian berita acara ini dibuat dengan sebenar-benarnya untuk dipergunakan sebagai landasan penetapan Duta Wisata Favorit Daerah Kabupaten Ponorogo.
  </p>

  <!-- Kolom Tanda Tangan -->
  <div class="row text-center mt-5 pt-3">
    <div class="col-6">
      Mengetahui,<br>
      <strong>Kepala Dinas Kebudayaan, Pariwisata,<br>Pemuda dan Olahraga Kabupaten Ponorogo</strong>
      <br><br><br><br><br>
      <strong class="text-decoration-underline">Dr. H. YUDI PRASETYO, M.Si.</strong><br>
      Pembina Utama Muda<br>
      NIP. 19750812 200003 1 002
    </div>
    <div class="col-6">
      Ponorogo, <?= format_tanggal_indo(date('Y-m-d'), false) ?><br>
      <strong>Ketua Panitia Pelaksana Pemilihan<br>Kakang Senduk Ponorogo <?= date('Y') ?></strong>
      <br><br><br><br><br>
      <strong class="text-decoration-underline">BAMBANG SURYONO, S.Sos.</strong><br>
      Ketua Panitia Pemilihan
    </div>
  </div>
</div>

</body>
</html>

