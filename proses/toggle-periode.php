<?php
/**
 * Backend Toggle Buka / Tutup Periode Voting
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    redirect(BASE_URL . 'admin/periode.php');
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf_token)) {
    set_flash('danger', 'Sesi keamanan berakhir (Invalid CSRF). Silakan coba lagi.');
    redirect(BASE_URL . 'admin/periode.php');
}

$periode_id = isset($_POST['periode_id']) ? (int)$_POST['periode_id'] : 0;
$action = trim($_POST['action'] ?? '');

if ($periode_id <= 0 || !$pdo) {
    set_flash('danger', 'Parameter permintaan tidak valid.');
    redirect(BASE_URL . 'admin/periode.php');
}

try {
    $stmt = $pdo->prepare("SELECT id, nama_periode, status FROM periode_voting WHERE id = ? LIMIT 1");
    $stmt->execute([$periode_id]);
    $periode = $stmt->fetch();

    if (!$periode) {
        set_flash('danger', 'Periode voting tidak ditemukan.');
        redirect(BASE_URL . 'admin/periode.php');
    }

    if ($action === 'activate') {
        // Nonaktifkan semua periode lain
        $pdo->query("UPDATE periode_voting SET status = 'nonaktif'");
        // Aktifkan periode ini
        $stmtAct = $pdo->prepare("UPDATE periode_voting SET status = 'aktif', updated_at = NOW() WHERE id = ?");
        $stmtAct->execute([$periode_id]);

        log_activity(
            $pdo,
            $_SESSION['user_id'],
            "Mengaktifkan periode voting: '{$periode['nama_periode']}'",
            'Periode'
        );

        set_flash('success', "Periode '{$periode['nama_periode']}' berhasil diaktifkan. Proses voting sekarang dibuka.");
    } else {
        // Nonaktifkan periode
        $stmtDeact = $pdo->prepare("UPDATE periode_voting SET status = 'nonaktif', updated_at = NOW() WHERE id = ?");
        $stmtDeact->execute([$periode_id]);

        log_activity(
            $pdo,
            $_SESSION['user_id'],
            "Menonaktifkan / menutup periode voting: '{$periode['nama_periode']}'",
            'Periode'
        );

        set_flash('info', "Periode '{$periode['nama_periode']}' berhasil dinonaktifkan / ditutup.");
    }

    redirect(BASE_URL . 'admin/periode.php');

} catch (PDOException $e) {
    error_log("Error in toggle-periode.php: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan sistem saat mengubah status periode.');
    redirect(BASE_URL . 'admin/periode.php');
}

