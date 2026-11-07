<?php
/**
 * Endpoint JSON Pembaruan Klasemen Real-Time (AJAX API)
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database tidak terhubung']);
    exit;
}

$periode = get_active_periode($pdo);
if (!$periode) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada periode aktif']);
    exit;
}

$p_id = (int)$periode['id'];

try {
    // 1. Total Suara per Kategori
    $stmtTotK = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Kakang'");
    $stmtTotK->execute([$p_id]);
    $total_kakang = (int)$stmtTotK->fetchColumn();

    $stmtTotS = $pdo->prepare("SELECT COUNT(*) FROM voting WHERE periode_voting_id = ? AND kategori = 'Senduk'");
    $stmtTotS->execute([$p_id]);
    $total_senduk = (int)$stmtTotS->fetchColumn();

    // 2. Data Klasemen Kakang
    $stmtKakang = $pdo->prepare("
        SELECT 
            k.id, k.nomor_peserta, k.nama, k.asal_instansi, k.foto,
            COUNT(v.id) AS total_suara,
            ROUND((COUNT(v.id) / NULLIF(:tot_k, 0)) * 100, 2) AS persentase
        FROM kontestan k
        LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
        WHERE k.kategori = 'Kakang' AND k.status = 'aktif'
        GROUP BY k.id
        ORDER BY total_suara DESC, k.nomor_peserta ASC
    ");
    $stmtKakang->execute([':tot_k' => $total_kakang, ':p_id' => $p_id]);
    $list_kakang = $stmtKakang->fetchAll();

    // 3. Data Klasemen Senduk
    $stmtSenduk = $pdo->prepare("
        SELECT 
            k.id, k.nomor_peserta, k.nama, k.asal_instansi, k.foto,
            COUNT(v.id) AS total_suara,
            ROUND((COUNT(v.id) / NULLIF(:tot_s, 0)) * 100, 2) AS persentase
        FROM kontestan k
        LEFT JOIN voting v ON k.id = v.kontestan_id AND v.periode_voting_id = :p_id
        WHERE k.kategori = 'Senduk' AND k.status = 'aktif'
        GROUP BY k.id
        ORDER BY total_suara DESC, k.nomor_peserta ASC
    ");
    $stmtSenduk->execute([':tot_s' => $total_senduk, ':p_id' => $p_id]);
    $list_senduk = $stmtSenduk->fetchAll();

    echo json_encode([
        'success' => true,
        'timestamp' => date('d M Y, H:i:s') . ' WIB',
        'kakang' => [
            'total_suara' => $total_kakang,
            'items' => $list_kakang
        ],
        'senduk' => [
            'total_suara' => $total_senduk,
            'items' => $list_senduk
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

