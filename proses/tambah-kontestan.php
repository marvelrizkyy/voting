<?php
/**
 * Backend Tambah Kontestan Baru
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

$csrf_token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf_token)) {
    set_flash('danger', 'Sesi keamanan berakhir (Invalid CSRF). Silakan coba lagi.');
    redirect(BASE_URL . 'admin/tambah-kontestan.php');
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

// Validasi Form
if (empty($nomor_peserta) || empty($nama) || empty($kategori) || empty($asal_instansi)) {
    set_flash('danger', 'Kolom nomor peserta, nama, kategori, dan asal instansi wajib diisi.');
    redirect(BASE_URL . 'admin/tambah-kontestan.php');
}

if (!in_array($kategori, ['Kakang', 'Senduk'])) {
    set_flash('danger', 'Kategori pemilihan tidak sah.');
    redirect(BASE_URL . 'admin/tambah-kontestan.php');
}

if (!$pdo) {
    set_flash('danger', 'Koneksi database terputus.');
    redirect(BASE_URL . 'admin/tambah-kontestan.php');
}

try {
    // Periksa keunikan nomor peserta
    $stmtCheck = $pdo->prepare("SELECT id FROM kontestan WHERE nomor_peserta = ? LIMIT 1");
    $stmtCheck->execute([$nomor_peserta]);
    if ($stmtCheck->fetch()) {
        set_flash('danger', "Nomor peserta '{$nomor_peserta}' sudah digunakan oleh kontestan lain.");
        redirect(BASE_URL . 'admin/tambah-kontestan.php');
    }

    // Validasi Upload Berkas Foto
    if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        set_flash('danger', 'Foto resmi kontestan wajib diunggah.');
        redirect(BASE_URL . 'admin/tambah-kontestan.php');
    }

    $file = $_FILES['foto'];
    $max_size = 2 * 1024 * 1024; // 2 MB
    if ($file['size'] > $max_size) {
        set_flash('danger', 'Ukuran berkas foto melebihi batas maksimal 2 MB.');
        redirect(BASE_URL . 'admin/tambah-kontestan.php');
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions)) {
        set_flash('danger', 'Format foto tidak valid. Gunakan format JPG, JPEG, PNG, atau WEBP.');
        redirect(BASE_URL . 'admin/tambah-kontestan.php');
    }

    // Verifikasi MIME Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowed_mimes)) {
        set_flash('danger', 'Tipe MIME berkas mencurigakan atau tidak sesuai dengan format gambar standar.');
        redirect(BASE_URL . 'admin/tambah-kontestan.php');
    }

    // Buat nama berkas acak unik
    $new_filename = 'kontestan_' . bin2hex(random_bytes(8)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $target_dir = __DIR__ . '/../uploads/kontestan/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $target_path = $target_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        set_flash('danger', 'Gagal memindahkan berkas foto ke server storage.');
        redirect(BASE_URL . 'admin/tambah-kontestan.php');
    }

    // Simpan ke database
    $stmtInsert = $pdo->prepare("
        INSERT INTO kontestan (
            nomor_peserta, nama, kategori, tempat_lahir, tanggal_lahir, 
            asal_instansi, foto, visi, misi, prestasi, deskripsi, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmtInsert->execute([
        $nomor_peserta, $nama, $kategori, $tempat_lahir, $tanggal_lahir,
        $asal_instansi, $new_filename, $visi, $misi, $prestasi, $deskripsi, $status
    ]);

    $kontestan_id = (int)$pdo->lastInsertId();

    // Catat log
    log_activity(
        $pdo,
        $_SESSION['user_id'],
        "Menambahkan kontestan baru: {$nomor_peserta} - {$nama} ({$kategori})",
        'Kontestan'
    );

    set_flash('success', "Kontestan {$nomor_peserta} — {$nama} berhasil ditambahkan ke dalam sistem!");
    redirect(BASE_URL . 'admin/kontestan.php');

} catch (PDOException $e) {
    error_log("Error in tambah-kontestan.php: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan sistem saat menyimpan data kontestan.');
    redirect(BASE_URL . 'admin/tambah-kontestan.php');
}

