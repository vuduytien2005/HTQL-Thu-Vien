<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền cập nhật sách.");
}

$ma_sach = $_GET['ma_sach'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_sach = $_POST['ten_sach'];
    $gia_tien = $_POST['gia_tien'];
    $trang_thai = $_POST['trang_thai'];

    $stmt = $pdo->prepare("UPDATE SACH SET Ten_sach = ?, Gia_tien = ?, Trang_thai = ? WHERE Ma_sach = ?");
    $stmt->execute([$ten_sach, $gia_tien, $trang_thai, $ma_sach]);

    echo "✅ Đã cập nhật sách.";
}

$stmt = $pdo->prepare("SELECT * FROM SACH WHERE Ma_sach = ?");
$stmt->execute([$ma_sach]);
$sach = $stmt->fetch();
?>

<form method="POST">
    <input type="text" name="ten_sach" value="<?= $sach['Ten_sach'] ?>" required>
    <input type="number" step="0.01" name="gia_tien" value="<?= $sach['Gia_tien'] ?>">
    <select name="trang_thai">
        <option value="Còn" <?= $sach['Trang_thai'] === 'Còn' ? 'selected' : '' ?>>Còn</option>
        <option value="Hết" <?= $sach['Trang_thai'] === 'Hết' ? 'selected' : '' ?>>Hết</option>
        <option value="Ngưng sử dụng" <?= $sach['Trang_thai'] === 'Ngưng sử dụng' ? 'selected' : '' ?>>Ngưng sử dụng</option>
    </select>
    <button type="submit">Cập nhật</button>
</form>