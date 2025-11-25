<?php
// get_dashboard_stats.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

try {
    // Tổng số quản trị viên
    $stmt_admins = $pdo->query("SELECT COUNT(*) as total FROM TAI_KHOAN WHERE role = 'admin'");
    $total_admins = $stmt_admins->fetchColumn();
    
    // Tổng số sách
    $stmt_books = $pdo->query("SELECT COUNT(*) as total FROM SACH");
    $total_books = $stmt_books->fetchColumn();
    
    // Tổng số độc giả
    $stmt_readers = $pdo->query("SELECT COUNT(*) as total FROM TAI_KHOAN WHERE role = 'docgia'");
    $total_readers = $stmt_readers->fetchColumn();
    
    // Tổng lượt mượn sách
    $stmt_borrows = $pdo->query("SELECT COUNT(*) as total FROM PHIEU_MUON");
    $total_borrows = $stmt_borrows->fetchColumn();
    
    // Tổng tiền phạt
    $stmt_fines = $pdo->query("SELECT COALESCE(SUM(Tien_phat), 0) as total FROM CHI_TIET_MUON");
    $total_fines = $stmt_fines->fetchColumn();

    echo json_encode([
        'success' => true,
        'total_admins' => (int)$total_admins,
        'total_books' => (int)$total_books,
        'total_readers' => (int)$total_readers,
        'total_borrows' => (int)$total_borrows,
        'total_fines' => (float)$total_fines
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>