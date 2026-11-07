<?php
/**
 * Backend Ubah Data Kontestan
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
    set_flash('danger', 'Sesi keamanan tidak sah (Invalid CSRF). Silakan coba lagi.');
    redirect(BASE_URL . 'admin/edit-kontestan.php?id=' . $id);
}

if ($id <= 0 || !$pdo) {
    set_flash('danger', 'Parameter tidak sah atau database terputus.');
    redirect(BASE_URL . 'admin/kontestan.php');
}

try {
    // Ambil data kontestan lama
    $stmtOld = $pdo->prepare("SELECT * FROM kontestan WHERE id = ? LIMIT 1");
    $stmtOld->execute([$id]);
    $old_data = $stmtOld->fetch();

    if (!$old_data) {
        set_flash('danger', 'Kontestan tidak ditemukan.');
        redirect(BASE_URL . 'admin/kontestan.php');
    }

    $nomor_peserta = strtoupper(trim($_POST['nomor_peserta'] ?? ''));
    $nama = trim($_POST['nama'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
    $asal_instansi = trim($_POST['asal_instansi'] ?? '');
    $visi = trim($_POST['visi'] ?? '');
    $misi = trim($_POST['misi'] ?? '');
    $prestasi = trim($_POST['prestasi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['aktif', 'nonaktif']) ? $_POST['status'] : 'aktif';

    if (empty($nomor_peserta) || empty($nama) || empty($kategori) || empty($asal_instansi)) {
        set_flash('danger', 'Kolom nomor peserta, nama, kategori, dan asal instansi wajib diisi.');
        redirect(BASE_URL . 'admin/edit-kontestan.php?id=' . $id);
    }

    // Periksa keunikan nomor peserta terhadap kontestan lain
    $stmtCheck = $pdo->prepare("SELECT id FROM kontestan WHERE nomor_peserta = ? AND id != ? LIMIT 1");
    $stmtCheck->execute([$nomor_peserta, $id]);
    if ($stmtCheck->fetch()) {
        set_flash('danger', "Nomor peserta '{$nomor_peserta}' sudah digunakan oleh kontestan lain.");
        redirect(BASE_URL . 'admin/edit-kontestan.php?id=' . $id);
    }

    $photo_filename = $old_data['foto'];

    // Jika admin mengunggah foto pengganti
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        $max_size = 2 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            set_flash('danger', 'Ukuran foto pengganti melebihi 2 MB.');
            redirect(BASE_URL . 'admin/edit-kontestan.php?id=' . $id);
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_extensions)) {
            set_flash('danger', 'Format foto baru tidak didukung (Gunakan JPG, PNG, atau WEBP).');
            redirect(BASE_URL . 'admin/edit-kontestan.php?id=' . $id);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed_mimes)) {
            set_flash('danger', 'Tipe MIME berkas gambar mencurigakan.');
            redirect(BASE_URL . 'admin/edit-kontestan.php?id=' . $id);
        }

        $new_filename = 'kontestan_' . bin2hex(random_bytes(8)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $target_path = __DIR__ . '/../uploads/kontestan/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $photo_filename = $new_filename;
        }
    }

    // Eksekusi update
    $stmtUpdate = $pdo->prepare("
        UPDATE kontestan SET
            nomor_peserta = ?, nama = ?, kategori = ?, tempat_lahir = ?, tanggal_lahir = ?,
            asal_instansi = ?, foto = ?, visi = ?, misi = ?, prestasi = ?, deskripsi = ?, 
            status = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmtUpdate->execute([
        $nomor_peserta, $nama, $kategori, $tempat_lahir, $tanggal_lahir,
        $asal_instansi, $photo_filename, $visi, $misi, $prestasi, $deskripsi,
        $status, $id
    ]);

    // Catat audit trail
    log_activity(
        $pdo,
        $_SESSION['user_id'],
        "Memperbarui data kontestan: {$nomor_peserta} - {$nama}",
        'Kontestan'
    );

    set_flash('success', "Perubahan data kontestan {$nomor_peserta} berhasil disimpan!");
    redirect(BASE_URL . 'admin/kontestan.php');

} catch (PDOException $e) {
    error_log("Error in edit-kontestan.php: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan sistem saat memperbarui data kontestan.');
    redirect(BASE_URL . 'admin/edit-kontestan.php?id=' . $id);
}

