<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'docgia') {
    die("Bạn không có quyền trả sách.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_phieu = $_POST['ma_phieu'];
    $ma_sach = $_POST['ma_sach'];
    $ngay_tra = date('Y-m-d');

    // Cập nhật ngày trả
    $stmt = $pdo->prepare("UPDATE CHI_TIET_MUON SET Ngay_tra_thuc_te = ? WHERE Ma_phieu_muon = ? AND Ma_sach = ?");
    $stmt->execute([$ngay_tra, $ma_phieu, $ma_sach]);

    // Giảm số bản đang mượn
    $pdo->prepare("UPDATE SACH SET So_ban_dang_muon = So_ban_dang_muon - 1 WHERE Ma_sach = ?")->execute([$ma_sach]);

    echo "✅ Đã trả sách.";
}
?>

<form method="POST">
    <input type="text" name="ma_phieu" placeholder="Mã phiếu mượn" required>
    <input type="text" name="ma_sach" placeholder="Mã sách" required>
    <button type="submit">Trả sách</button>
</form>