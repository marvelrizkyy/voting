<?php
/**
 * Konfigurasi Aplikasi Utama
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

// Set Zona Waktu Resmi WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

// Start PHP Session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    // Pengaturan cookie sesi aman
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Deteksi Otomatis Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$project_folder = '/voting-kakang-senduk/';

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . $host . $project_folder);
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Kakang Senduk Ponorogo');
}

if (!defined('APP_TITLE')) {
    define('APP_TITLE', 'Pemilihan Duta Wisata Daerah Kabupaten Ponorogo');
}

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../uploads/kontestan/');
}

if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', BASE_URL . 'uploads/kontestan/');
}

