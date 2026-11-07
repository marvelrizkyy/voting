<?php
/**
 * Backend Generator Ekspor Excel (.CSV Berstandar Spreadsheet)
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

// Query Data Hasil
$stmtResults = $pdo->prepare("
    SELECT 
        k.nomor_peserta, k.nama, k.kategori, k.asal_instansi,
        COUNT(v.id) AS total_suara,
        CASE 
            WHEN k.kategori = 'Kakang' THEN ROUND((COUNT(v.id) / NULLIF(:tot_k, 0)) * 100, 2)
            ELSE ROUND((COUNT(v.id) / NULLIF(:tot_s, 0)) * 100, 2)
        END AS persentase
    FROM kontestan k
    LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
    WHERE k.status = 'aktif'
    GROUP BY k.id
    ORDER BY k.kategori ASC, total_suara DESC, k.nomor_peserta ASC
");
$stmtResults->execute([
    ':tot_k' => $total_kakang,
    ':tot_s' => $total_senduk,
    ':p_id'  => $periode_id
]);
$rows = $stmtResults->fetchAll();

// Log Ekspor
log_activity(
    $pdo,
    $_SESSION['user_id'],
    "Mengekspor laporan hasil voting periode '{$periode['nama_periode']}' ke format Excel",
    'Laporan'
);

// Header HTTP untuk Unduhan Excel (CSV dengan pemisah koma / UTF-8 BOM)
$filename = "Laporan_Voting_Kakang_Senduk_" . preg_replace('/[^A-Za-z0-9_]/', '_', $periode['nama_periode']) . "_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Masukkan UTF-8 BOM agar Excel membuka karakter dengan benar
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Judul Laporan
fputcsv($output, ['LAPORAN RESMI REKAPITULASI HASIL VOTING PEMILIHAN KAKANG SENDUK PONOROGO']);
fputcsv($output, ['Periode Pemilihan:', $periode['nama_periode']]);
fputcsv($output, ['Tanggal Pelaksanaan:', $periode['tanggal_mulai'] . ' s/d ' . $periode['tanggal_selesai']]);
fputcsv($output, ['Tanggal Unduh:', date('d F Y, H:i:s') . ' WIB']);
fputcsv($output, ['Total Suara Masuk:', ($total_kakang + $total_senduk) . ' Suara (Kakang: ' . $total_kakang . ', Senduk: ' . $total_senduk . ')']);
fputcsv($output, []); // Baris Kosong

// Header Kolom Tabel
fputcsv($output, ['Ranking', 'Kategori', 'Nomor Peserta', 'Nama Kontestan', 'Asal Sekolah / Instansi', 'Jumlah Suara Sah', 'Persentase (%)']);

$curr_kat = '';
$rank = 0;
foreach ($rows as $r) {
    if ($r['kategori'] !== $curr_kat) {
        $curr_kat = $r['kategori'];
        $rank = 1;
    } else {
        $rank++;
    }

    fputcsv($output, [
        $rank,
        $r['kategori'],
        $r['nomor_peserta'],
        $r['nama'],
        $r['asal_instansi'],
        $r['total_suara'],
        ($r['persentase'] ?: '0.00') . '%'
    ]);
}

fclose($output);
exit;

