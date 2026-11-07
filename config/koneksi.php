<?php
/**
 * Koneksi Database Menggunakan PDO
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/app.php';

$db_host = '127.0.0.1';
$db_port = '3306';
$db_name = 'db_voting_kakang_senduk';
$db_user = 'root';
$db_pass = '';

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Catat log error internal tanpa membocorkan kredensial ke publik
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Simpan status koneksi untuk pengecekan anggun (graceful handling)
    $db_connection_error = true;
    $pdo = null;
}

