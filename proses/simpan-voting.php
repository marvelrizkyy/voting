<?php
/**
 * Backend Pemrosesan Voting (Atomic Transaction & Anti-Double Vote)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    set_flash('danger', 'Metode permintaan tidak diizinkan.');
    redirect(BASE_URL . 'pages/voting.php');
}

// 1. Validasi Autentikasi Pengguna
if (!is_logged_in()) {
    set_flash('warning', 'Silakan masuk (login) terlebih dahulu untuk memberikan suara.');
    redirect(BASE_URL . 'auth/login.php');
}

if (is_admin()) {
    set_flash('info', 'Administrator bertindak sebagai pengawas netral dan tidak diperkenankan memberikan suara voting.');
    redirect(BASE_URL . 'admin/dashboard.php');
}

$user_id = (int)$_SESSION['user_id'];
$csrf_token = $_POST['csrf_token'] ?? '';
$kontestan_id = isset($_POST['kontestan_id']) ? (int)$_POST['kontestan_id'] : 0;
$kategori = trim($_POST['kategori'] ?? '');

// 2. Validasi CSRF Token
if (!verify_csrf_token($csrf_token)) {
    set_flash('danger', 'Sesi keamanan kedaluwarsa (Invalid CSRF). Silakan refresh halaman dan ulangi proses voting.');
    redirect(BASE_URL . 'pages/voting.php');
}

// 3. Validasi Input Parameter
if ($kontestan_id <= 0 || !in_array($kategori, ['Kakang', 'Senduk'])) {
    set_flash('danger', 'Data kontestan atau kategori yang dipilih tidak valid.');
    redirect(BASE_URL . 'pages/voting.php');
}

if (!$pdo) {
    set_flash('danger', 'Koneksi database bermasalah. Pastikan MySQL telah aktif.');
    redirect(BASE_URL . 'pages/voting.php');
}

try {
    // 4. Validasi Periode Voting Aktif & Jadwal
    $stmtPeriode = $pdo->prepare("
        SELECT id, nama_periode, tanggal_mulai, tanggal_selesai, status 
        FROM periode_voting 
        WHERE status = 'aktif' 
        ORDER BY id DESC LIMIT 1
    ");
    $stmtPeriode->execute();
    $periode = $stmtPeriode->fetch();

    if (!$periode) {
        set_flash('danger', 'Proses voting ditolak. Saat ini belum ada periode pemilihan yang aktif.');
        redirect(BASE_URL . 'pages/voting.php');
    }

    $now = date('Y-m-d H:i:s');
    if ($now < $periode['tanggal_mulai']) {
        set_flash('warning', 'Periode pemilihan "' . escape($periode['nama_periode']) . '" belum resmi dibuka (Mulai: ' . format_tanggal_indo($periode['tanggal_mulai']) . ').');
        redirect(BASE_URL . 'pages/voting.php');
    }

    if ($now > $periode['tanggal_selesai']) {
        set_flash('danger', 'Periode pemilihan "' . escape($periode['nama_periode']) . '" telah resmi ditutup pada ' . format_tanggal_indo($periode['tanggal_selesai']) . '.');
        redirect(BASE_URL . 'pages/voting.php');
    }

    $periode_id = (int)$periode['id'];

    // 5. MULAI TRANSAKSI DATABASE (ATOMIC TRANSACTION)
    $pdo->beginTransaction();

    // Verifikasi Kontestan: Harus aktif dan kategorinya sesuai
    $stmtKontestan = $pdo->prepare("SELECT id, nama, nomor_peserta, kategori, status FROM kontestan WHERE id = ? FOR UPDATE");
    $stmtKontestan->execute([$kontestan_id]);
    $kontestan = $stmtKontestan->fetch();

    if (!$kontestan || $kontestan['status'] !== 'aktif') {
        $pdo->rollBack();
        set_flash('danger', 'Kontestan yang Anda pilih tidak aktif atau tidak ditemukan.');
        redirect(BASE_URL . 'pages/voting.php');
    }

    if ($kontestan['kategori'] !== $kategori) {
        $pdo->rollBack();
        set_flash('danger', 'Kategori kontestan tidak sesuai dengan bilik voting yang dipilih.');
        redirect(BASE_URL . 'pages/voting.php');
    }

    // Verifikasi Suara Sebelumnya (Mencegah Race Condition & Double Voting)
    $stmtVoteCheck = $pdo->prepare("
        SELECT id FROM voting 
        WHERE user_id = ? AND kategori = ? AND periode_voting_id = ? 
        FOR UPDATE
    ");
    $stmtVoteCheck->execute([$user_id, $kategori, $periode_id]);
    $existing_vote = $stmtVoteCheck->fetch();

    if ($existing_vote) {
        $pdo->rollBack();
        set_flash('warning', 'Anda sudah memberikan suara untuk kategori ' . escape($kategori) . ' pada periode ini. Suara yang sudah masuk tidak dapat diubah.');
        redirect(BASE_URL . 'pages/voting.php?tab=' . strtolower($kategori));
    }

    // Simpan Suara ke Database
    $stmtInsertVote = $pdo->prepare("
        INSERT INTO voting (user_id, kontestan_id, periode_voting_id, kategori, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmtInsertVote->execute([$user_id, $kontestan_id, $periode_id, $kategori]);

    // COMMIT TRANSAKSI
    $pdo->commit();

    // Catat log aktivitas
    log_activity(
        $pdo,
        $user_id,
        "Memberikan suara sah pada kategori {$kategori} untuk {$kontestan['nomor_peserta']} - {$kontestan['nama']}",
        'Voting'
    );

    set_flash('success', "Suara Anda berhasil tercatat untuk {$kontestan['nomor_peserta']} — {$kontestan['nama']} (Kategori {$kategori}). Terima kasih atas partisipasi aktif Anda!");
    
    // Tentukan redirect tab berikutnya jika kategori lain belum dipilih
    $vote_status = get_user_vote_status($pdo, $user_id, $periode_id);
    if ($kategori === 'Kakang' && !$vote_status['senduk']) {
        redirect(BASE_URL . 'pages/voting.php?tab=senduk');
    } elseif ($kategori === 'Senduk' && !$vote_status['kakang']) {
        redirect(BASE_URL . 'pages/voting.php?tab=kakang');
    } else {
        redirect(BASE_URL . 'pages/voting.php');
    }

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Tangani kemungkinan duplikasi key error
    if ($e->getCode() == 23000) {
        set_flash('warning', 'Pencegahan suara ganda: Anda telah memberikan suara untuk kategori ini sebelumnya.');
    } else {
        error_log("Voting Transaction Error: " . $e->getMessage());
        set_flash('danger', 'Terjadi gangguan sistem saat memproses suara Anda. Silakan coba beberapa saat lagi.');
    }
    redirect(BASE_URL . 'pages/voting.php');
}

