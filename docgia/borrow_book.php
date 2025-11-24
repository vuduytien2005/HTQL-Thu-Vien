<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'docgia') {
    die("Bạn không có quyền mượn sách.");
}

$ma_doc_gia = $_SESSION['user']['username']; // giả sử username trùng mã độc giả

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_sach = $_POST['ma_sach'];
    $ma_phieu = 'PM' . time();
    $ngay_muon = date('Y-m-d');
    $ngay_hen_tra = date('Y-m-d', strtotime('+7 days'));

    // Tạo phiếu mượn
    $stmt = $pdo->prepare("INSERT INTO PHIEU_MUON (Ma_phieu_muon, Ma_doc_gia, Ma_nhan_vien, Ngay_muon, Ngay_hen_tra, Tong_so_sach, Trang_thai)
                           VALUES (?, ?, 'NV001', ?, ?, 1, 'Đang mượn')");
    $stmt->execute([$ma_phieu, $ma_doc_gia, $ngay_muon, $ngay_hen_tra]);

    // Chi tiết mượn
    $stmt = $pdo->prepare("INSERT INTO CHI_TIET_MUON (Ma_phieu_muon, Ma_sach) VALUES (?, ?)");
    $stmt->execute([$ma_phieu, $ma_sach]);

    // Cập nhật số bản đang mượn
    $pdo->prepare("UPDATE SACH SET So_ban_dang_muon = So_ban_dang_muon + 1 WHERE Ma_sach = ?")->execute([$ma_sach]);

    echo "✅ Đã mượn sách thành công.";
}
?>

<form method="POST">
    <input type="text" name="ma_sach" placeholder="Mã sách cần mượn" required>
    <button type="submit">Mượn sách</button>
</form>