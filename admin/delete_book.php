<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền xóa sách.");
}

$ma_sach = $_GET['ma_sach'] ?? null;

if ($ma_sach) {
    try {
        // Kiểm tra xem sách có tồn tại không
        $check_stmt = $pdo->prepare("SELECT Ten_sach FROM SACH WHERE Ma_sach = ?");
        $check_stmt->execute([$ma_sach]);
        $book = $check_stmt->fetch();
        
        if ($book) {
            $stmt = $pdo->prepare("DELETE FROM SACH WHERE Ma_sach = ?");
            $stmt->execute([$ma_sach]);
            header('Location: list_book.php?delete_success=Đã xóa sách: ' . $book['Ten_sach']);
            exit();
        } else {
            header('Location: list_book.php?delete_error=Không tìm thấy sách để xóa');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: list_book.php?delete_error=Lỗi khi xóa sách: ' . $e->getMessage());
        exit();
    }
} else {
    header('Location: list_book.php?delete_error=Không tìm thấy mã sách');
    exit();
}
?>