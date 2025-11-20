<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền thêm sách.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_sach = $_POST['ma_sach'];
    $ten_sach = $_POST['ten_sach'];
    $nam_xuat_ban = $_POST['nam_xuat_ban'];
    $nha_xuat_ban = $_POST['nha_xuat_ban'];
    $gia_tien = $_POST['gia_tien'];
    $so_ban = $_POST['so_ban'];
    $nguon_cung_cap = $_POST['nguon_cung_cap'];
    $trang_thai = $_POST['trang_thai'];

    $stmt = $pdo->prepare("INSERT INTO SACH (Ma_sach, Ten_sach, Nam_xuat_ban, Nha_xuat_ban, Gia_tien, So_ban, So_ban_dang_muon, Trang_thai, Nguon_cung_cap)
                           VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)");
    $stmt->execute([$ma_sach, $ten_sach, $nam_xuat_ban, $nha_xuat_ban, $gia_tien, $so_ban, $trang_thai, $nguon_cung_cap]);

    echo "✅ Đã thêm sách thành công.";
}
?>

<form method="POST">
    <input type="text" name="ma_sach" placeholder="Mã sách" required>
    <input type="text" name="ten_sach" placeholder="Tên sách" required>
    <input type="number" name="nam_xuat_ban" placeholder="Năm xuất bản">
    <input type="text" name="nha_xuat_ban" placeholder="Nhà xuất bản">
    <input type="number" step="0.01" name="gia_tien" placeholder="Giá tiền">
    <input type="number" name="so_ban" placeholder="Số bản">
    <input type="text" name="nguon_cung_cap" placeholder="Nhà cung cấp">
    <select name="trang_thai">
        <option value="Còn">Còn</option>
        <option value="Hết">Hết</option>
        <option value="Ngưng sử dụng">Ngưng sử dụng</option>
    </select>
    <button type="submit">Thêm sách</button>
</form>