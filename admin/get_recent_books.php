<?php
// get_recent_books.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT s.*, 
               GROUP_CONCAT(tl.Ten_the_loai SEPARATOR ', ') as Danh_sach_the_loai
        FROM SACH s
        LEFT JOIN sach_the_loai stl ON s.Ma_sach = stl.Ma_sach
        LEFT JOIN the_loai tl ON stl.Ma_the_loai = tl.Ma_the_loai
        GROUP BY s.Ma_sach
        ORDER BY s.Ma_sach DESC 
        LIMIT 5
    ");
    $books = $stmt->fetchAll();

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