<?php
/**
 * Global Utility Functions
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/csrf.php';

/**
 * Sanitasi output teks untuk mencegah Cross-Site Scripting (XSS)
 */
function escape(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Memeriksa apakah pengguna sedang login
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Memeriksa apakah pengguna memiliki hak akses Administrator
 */
function is_admin(): bool {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Memeriksa apakah pengguna adalah Pemilih
 */
function is_pemilih(): bool {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'pemilih';
}

/**
 * Mengambil informasi user yang sedang aktif
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'nama'  => $_SESSION['user_nama'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'pemilih',
    ];
}

/**
 * Menyimpan pesan flash untuk ditampilkan pada halaman berikutnya
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Mengambil dan membersihkan pesan flash dari session
 */
function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Merender pesan notifikasi flash dalam komponen Bootstrap alert
 */
function display_flash(): string {
    $flash = get_flash();
    if (!$flash) {
        return '';
    }

    $icon = match ($flash['type']) {
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        default   => 'bi-info-circle-fill'
    };

    $type = escape($flash['type']);
    $message = escape($flash['message']);

    return <<<HTML
    <div class="alert alert-{$type} alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
        <i class="bi {$icon} me-2 fs-5"></i>
        <div>{$message}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
HTML;
}

/**
 * Redirect ke URL tujuan
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

/**
 * Mengambil data periode voting yang sedang aktif saat ini
 */
function get_active_periode(?PDO $pdo): ?array {
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM periode_voting WHERE status = 'aktif' ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $periode = $stmt->fetch();
        return $periode ?: null;
    } catch (PDOException $e) {
        error_log("Error in get_active_periode: " . $e->getMessage());
        return null;
    }
}

/**
 * Memeriksa status voting pemilih pada suatu periode
 */
function get_user_vote_status(?PDO $pdo, int $user_id, ?int $periode_id): array {
    $status = [
        'kakang' => false,
        'senduk' => false,
        'kakang_kontestan_id' => null,
        'senduk_kontestan_id' => null,
        'total_suara' => 0
    ];

    if (!$pdo || empty($user_id) || empty($periode_id)) {
        return $status;
    }

    try {
        $stmt = $pdo->prepare("SELECT kontestan_id, kategori FROM voting WHERE user_id = ? AND periode_voting_id = ?");
        $stmt->execute([$user_id, $periode_id]);
        $votes = $stmt->fetchAll();

        foreach ($votes as $v) {
            if ($v['kategori'] === 'Kakang') {
                $status['kakang'] = true;
                $status['kakang_kontestan_id'] = (int)$v['kontestan_id'];
            } elseif ($v['kategori'] === 'Senduk') {
                $status['senduk'] = true;
                $status['senduk_kontestan_id'] = (int)$v['kontestan_id'];
            }
        }
        $status['total_suara'] = count($votes);
    } catch (PDOException $e) {
        error_log("Error in get_user_vote_status: " . $e->getMessage());
    }

    return $status;
}

/**
 * Menghasilkan URL foto kontestan yang valid atau fallback avatar
 */
function get_kontestan_photo_url(?string $filename, string $kategori = 'Kakang'): string {
    if (!empty($filename)) {
        $local_path = __DIR__ . '/../uploads/kontestan/' . $filename;
        if (file_exists($local_path)) {
            return BASE_URL . 'uploads/kontestan/' . urlencode($filename);
        }
    }
    
    // Fallback avatar dinamis
    return ($kategori === 'Senduk') 
        ? BASE_URL . 'assets/img/senduk-placeholder.svg' 
        : BASE_URL . 'assets/img/kakang-placeholder.svg';
}

/**
 * Format tanggal Indonesia lengkap
 */
function format_tanggal_indo(?string $datetime, bool $include_time = true): string {
    if (empty($datetime)) return '-';
    
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $time = strtotime($datetime);
    if (!$time) return '-';

    $hari = date('d', $time);
    $bln_idx = (int)date('m', $time);
    $tahun = date('Y', $time);

    $formatted = "{$hari} " . ($bulan[$bln_idx] ?? '') . " {$tahun}";
    if ($include_time) {
        $formatted .= ', ' . date('H:i', $time) . ' WIB';
    }

    return $formatted;
}

/**
 * Catat aktivitas penting ke audit trail activity_logs
 */
function log_activity(?PDO $pdo, int $user_id, string $aktivitas, string $modul): void {
    if (!$pdo) return;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, aktivitas, modul, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $aktivitas, $modul, $ip, $ua]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

