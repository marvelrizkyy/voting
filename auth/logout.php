<?php
/**
 * Logout Handler
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in() && $pdo) {
    log_activity($pdo, $_SESSION['user_id'], 'Pengguna keluar dari sistem (logout)', 'Autentikasi');
}

// Hancurkan seluruh data sesi
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Mulai sesi baru khusus untuk flash message
session_start();
set_flash('info', 'Anda telah berhasil keluar dari sistem. Terima kasih atas partisipasi Anda.');
redirect(BASE_URL . 'auth/login.php');

