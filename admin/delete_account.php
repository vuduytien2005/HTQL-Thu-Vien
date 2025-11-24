<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền xóa tài khoản.");
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM TAI_KHOAN WHERE id = ?");
    $stmt->execute([$id]);
    echo "✅ Đã xóa tài khoản.";
} else {
    echo "❌ Không tìm thấy ID.";
}
?>
<a href="list_accounts.php">Quay lại danh sách</a>