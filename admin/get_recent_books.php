<?php
// get_recent_books.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// DEBUG: Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Sửa query theo CSDL thực tế - KHÔNG JOIN với bảng không tồn tại
    $stmt = $pdo->query("
        SELECT 
            Ma_sach, 
            Ten_sach, 
            Ten_tac_gia,
            Nha_xuat_ban,
            Gia_tien,
            Trang_thai,
            So_ban,
            Ten_the_loai,
            Nam_xuat_ban,
            Noi_dung
        FROM SACH 
        ORDER BY Ma_sach DESC 
        LIMIT 5
    ");
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'books' => $books
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>