<?php
/**
 * Authentication & Authorization Middleware
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/functions.php';

/**
 * Memastikan pengguna telah login
 */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan masuk (login) terlebih dahulu untuk melanjutkan.');
        redirect(BASE_URL . 'auth/login.php');
    }
}

/**
 * Memastikan hanya Administrator yang berhak mengakses halaman
 */
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'Akses ditolak! Anda tidak memiliki hak akses Administrator.');
        redirect(BASE_URL . 'pages/403.php');
    }
}

/**
 * Memastikan hanya Pemilih aktif yang berhak mengakses halaman voting
 */
function require_pemilih(): void {
    require_login();
    if (is_admin()) {
        set_flash('info', 'Administrator bertindak sebagai pengelola netral dan tidak memberikan suara.');
        redirect(BASE_URL . 'admin/dashboard.php');
    }
}

/**
 * Mencegah pengguna yang sudah login mengakses halaman Login / Register
 */
function require_guest(): void {
    if (is_logged_in()) {
        if (is_admin()) {
            redirect(BASE_URL . 'admin/dashboard.php');
        } else {
            redirect(BASE_URL . 'index.php');
        }
    }
}

