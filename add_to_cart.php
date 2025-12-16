<?php
// add_to_cart.php
session_start();
require '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    // Lưu book_id vào session để sau khi login có thể thêm vào giỏ
    if (isset($_GET['book_id'])) {
        $_SESSION['book_id_to_borrow'] = $_GET['book_id'];
    }
    
    // Chuyển hướng đến login với redirect
    $redirect_url = urlencode('../docgia/view_book.php');
    header("Location: ../auth/login.php?redirect_to=$redirect_url");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['username'];

// Lấy mã sách từ URL
$book_id = $_GET['book_id'] ?? null;
if (!$book_id) {
    $_SESSION['error'] = 'Không tìm thấy sách!';
    header('Location: index.php');
    exit();
}

// Kiểm tra sách có tồn tại và có thể mượn không
$stmt = $pdo->prepare("
    SELECT * FROM SACH 
    WHERE Ma_sach = ? AND Trang_thai = 'Còn' 
    AND So_ban > So_ban_dang_muon
");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    $_SESSION['error'] = 'Sách không có sẵn để mượn!';
    header('Location: index.php');
    exit();
}

// Kiểm tra sách đã có trong giỏ chưa
$stmt = $pdo->prepare("SELECT * FROM gio_muon_tam WHERE Ma_doc_gia = ? AND Ma_sach = ?");
$stmt->execute([$user_id, $book_id]);

if ($stmt->rowCount() > 0) {
    $_SESSION['error'] = 'Sách đã có trong giỏ mượn!';
    header("Location: view_book.php?id=$book_id");
    exit();
}

// Thêm vào giỏ
$stmt = $pdo->prepare("INSERT INTO gio_muon_tam (Ma_doc_gia, Ma_sach, Ngay_them) VALUES (?, ?, NOW())");
if ($stmt->execute([$user_id, $book_id])) {
    $_SESSION['success'] = 'Đã thêm sách vào giỏ mượn thành công!';
} else {
    $_SESSION['error'] = 'Có lỗi xảy ra khi thêm vào giỏ!';
}

header("Location: view_book.php?id=$book_id");
exit();