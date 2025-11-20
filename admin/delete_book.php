<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền xóa sách.");
}

$ma_sach = $_GET['ma_sach'] ?? null;
if ($ma_sach) {
    $stmt = $pdo->prepare("DELETE FROM SACH WHERE Ma_sach = ?");
    $stmt->execute([$ma_sach]);
    echo "✅ Đã xóa sách.";
} else {
    echo "❌ Không tìm thấy mã sách.";
}
?>
<a href="list_books.php">Quay lại danh sách</a>