<?php
/**
 * Backend Hapus / Arsipkan Kontestan (Integritas Data Voting)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    redirect(BASE_URL . 'admin/kontestan.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$csrf_token = $_POST['csrf_token'] ?? '';

if (!verify_csrf_token($csrf_token)) {
    set_flash('danger', 'Sesi keamanan berakhir (Invalid CSRF). Silakan coba lagi.');
    redirect(BASE_URL . 'admin/kontestan.php');
}

if ($id <= 0 || !$pdo) {
    set_flash('danger', 'Parameter tidak sah atau database terputus.');
    redirect(BASE_URL . 'admin/kontestan.php');
}

try {
    $stmt = $pdo->prepare("SELECT id, nomor_peserta, nama, foto FROM kontestan WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $kontestan = $stmt->fetch();

    if (!$kontestan) {
        set_flash('danger', 'Kontestan tidak ditemukan.');
        redirect(BASE_URL . 'admin/kontestan.php');
    }

    // Periksa apakah kontestan telah menerima suara sah
    $stmtVotes = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE kontestan_id = ?");
    $stmtVotes->execute([$id]);
    $vote_count = (int)$stmtVotes->fetchColumn();

    if ($vote_count > 0) {
        // PERLINDUNGAN INTEGRITAS DATA: Ubah status menjadi nonaktif
        $stmtArchive = $pdo->prepare("UPDATE kontestan SET status = 'nonaktif', updated_at = NOW() WHERE id = ?");
        $stmtArchive->execute([$id]);

        log_activity(
            $pdo,
            $_SESSION['user_id'],
            "Menonaktifkan / mengarsipkan kontestan: {$kontestan['nomor_peserta']} - {$kontestan['nama']} (Memiliki {$vote_count} suara)",
            'Kontestan'
        );

        set_flash('warning', "Kontestan {$kontestan['nomor_peserta']} memiliki {$vote_count} suara sah. Sesuai standar integritas data, kontestan dinonaktifkan (diarsipkan) dan tidak dihapus permanen.");
    } else {
        // Kontestan belum memiliki suara, aman untuk dihapus permanen
        $stmtDelete = $pdo->prepare("DELETE FROM kontestan WHERE id = ?");
        $stmtDelete->execute([$id]);

        // Hapus berkas foto jika ada dan bukan berkas sistem
        if (!empty($kontestan['foto']) && str_starts_with($kontestan['foto'], 'kontestan_')) {
            $file_path = __DIR__ . '/../uploads/kontestan/' . $kontestan['foto'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        log_activity(
            $pdo,
            $_SESSION['user_id'],
            "Menghapus permanen kontestan tanpa suara: {$kontestan['nomor_peserta']} - {$kontestan['nama']}",
            'Kontestan'
        );

        set_flash('success', "Kontestan {$kontestan['nomor_peserta']} — {$kontestan['nama']} berhasil dihapus dari sistem.");
    }

    redirect(BASE_URL . 'admin/kontestan.php');

} catch (PDOException $e) {
    error_log("Error in hapus-kontestan.php: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan sistem saat memproses penghapusan kontestan.');
    redirect(BASE_URL . 'admin/kontestan.php');
}

