<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền tạo báo cáo.");
}

// Thống kê dữ liệu
$total_books = $pdo->query("SELECT COUNT(*) FROM SACH")->fetchColumn();
$total_readers = $pdo->query("SELECT COUNT(*) FROM DOC_GIA")->fetchColumn();
$total_borrows = $pdo->query("SELECT COUNT(*) FROM PHIEU_MUON")->fetchColumn();
$total_fines = $pdo->query("SELECT SUM(Tien_phat) FROM CHI_TIET_MUON")->fetchColumn();

$du_lieu = json_encode([
    'Tổng số sách' => $total_books,
    'Tổng số độc giả' => $total_readers,
    'Tổng số lượt mượn' => $total_borrows,
    'Tổng tiền phạt' => $total_fines ?? 0
]);

$loai_bao_cao = 'Thống kê tổng quan';
$nguoi_tao = $_SESSION['user']['username'];

$stmt = $pdo->prepare("INSERT INTO BAO_CAO_THONG_KE (Loai_bao_cao, Nguoi_tao, Du_lieu) VALUES (?, ?, ?)");
$stmt->execute([$loai_bao_cao, $nguoi_tao, $du_lieu]);

echo "✅ Đã tạo báo cáo thống kê.";
?>