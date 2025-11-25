<?php
// get_recent_reports.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM BAO_CAO_THONG_KE ORDER BY Thoi_gian_tao DESC LIMIT 5");
    $reports = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'reports' => $reports
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>