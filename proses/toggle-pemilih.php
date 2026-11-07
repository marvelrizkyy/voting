<?php
/**
 * Backend Toggle Status Akun Pemilih
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    redirect(BASE_URL . 'admin/pemilih.php');
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf_token)) {
    set_flash('danger', 'Sesi keamanan berakhir (Invalid CSRF). Silakan coba lagi.');
    redirect(BASE_URL . 'admin/pemilih.php');
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$action = trim($_POST['action'] ?? '');

if ($user_id <= 0 || !$pdo) {
    set_flash('danger', 'Parameter permintaan tidak valid.');
    redirect(BASE_URL . 'admin/pemilih.php');
}

try {
    $stmt = $pdo->prepare("SELECT id, nama, email, role, status FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        set_flash('danger', 'Data akun pemilih tidak ditemukan.');
        redirect(BASE_URL . 'admin/pemilih.php');
    }

    if ($user['role'] === 'admin') {
        set_flash('danger', 'Status akun Administrator tidak dapat diubah dari modul ini.');
        redirect(BASE_URL . 'admin/pemilih.php');
    }

    $new_status = ($action === 'activate') ? 'aktif' : 'nonaktif';
    $stmtUpdate = $pdo->prepare("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->execute([$new_status, $user_id]);

    log_activity(
        $pdo,
        $_SESSION['user_id'],
        "Mengubah status akun pemilih '{$user['nama']}' ({$user['email']}) menjadi '{$new_status}'",
        'Pemilih'
    );

    $msg = ($new_status === 'aktif') 
        ? "Akun pemilih '{$user['nama']}' berhasil diaktifkan kembali."
        : "Akun pemilih '{$user['nama']}' berhasil dinonaktifkan / diblokir.";

    set_flash('success', $msg);
    redirect(BASE_URL . 'admin/pemilih.php');

} catch (PDOException $e) {
    error_log("Error in toggle-pemilih.php: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan sistem saat memperbarui status pemilih.');
    redirect(BASE_URL . 'admin/pemilih.php');
}

