<?php
/**
 * Backend Simpan Periode Voting (Tambah & Edit)
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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nama_periode = trim($_POST['nama_periode'] ?? '');
$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status = in_array($_POST['status'] ?? '', ['aktif', 'nonaktif']) ? $_POST['status'] : 'nonaktif';

if (empty($nama_periode) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
    set_flash('danger', 'Seluruh kolom formulir periode wajib diisi.');
    redirect(BASE_URL . 'admin/periode.php');
}

if ($tanggal_mulai >= $tanggal_selesai) {
    set_flash('danger', 'Tanggal selesai voting harus lebih lambat dari tanggal mulai.');
    redirect(BASE_URL . 'admin/periode.php');
}

if (!$pdo) {
    set_flash('danger', 'Koneksi database terputus.');
    redirect(BASE_URL . 'admin/periode.php');
}

try {
    // Jika periode di-set aktif, nonaktifkan periode lain untuk menghindari tumpang tindih
    if ($status === 'aktif') {
        $pdo->query("UPDATE periode_voting SET status = 'nonaktif'");
    }

    if ($id > 0) {
        // Update periode
        $stmtUpdate = $pdo->prepare("
            UPDATE periode_voting SET
                nama_periode = ?, tanggal_mulai = ?, tanggal_selesai = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUpdate->execute([$nama_periode, $tanggal_mulai, $tanggal_selesai, $status, $id]);

        log_activity(
            $pdo,
            $_SESSION['user_id'],
            "Memperbarui konfigurasi periode voting: '{$nama_periode}' (Status: {$status})",
            'Periode'
        );

        set_flash('success', "Periode '{$nama_periode}' berhasil diperbarui.");
    } else {
        // Insert periode baru
        $stmtInsert = $pdo->prepare("
            INSERT INTO periode_voting (nama_periode, tanggal_mulai, tanggal_selesai, status, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmtInsert->execute([$nama_periode, $tanggal_mulai, $tanggal_selesai, $status]);

        log_activity(
            $pdo,
            $_SESSION['user_id'],
            "Membuat periode voting baru: '{$nama_periode}'",
            'Periode'
        );

        set_flash('success', "Periode voting baru '{$nama_periode}' berhasil dibuat.");
    }

    redirect(BASE_URL . 'admin/periode.php');

} catch (PDOException $e) {
    error_log("Error in simpan-periode.php: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan sistem saat menyimpan periode voting.');
    redirect(BASE_URL . 'admin/periode.php');
}

