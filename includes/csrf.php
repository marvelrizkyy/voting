<?php
/**
 * CSRF Protection Helper
 * Menghasilkan dan memverifikasi token CSRF per sesi pengguna
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Menghasilkan atau mengambil token CSRF dari session
 */
function get_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Merender elemen input tersembunyi berisi CSRF token
 */
function csrf_input(): string {
    $token = htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Memvalidasi token CSRF dari request POST
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

