<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'docgia') {
    header('Location: ../auth/login.php');
    exit();
}

$user = $_SESSION['user'];
$username = $user['username'];

if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];
    
    // Kiểm tra sách có tồn tại không
    $stmt = $pdo->prepare("SELECT * FROM SACH WHERE Ma_sach = ? AND Trang_thai = 'Còn'");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if ($book) {
        // Kiểm tra sách đã có trong giỏ chưa
        $check_stmt = $pdo->prepare("SELECT * FROM gio_muon_tam WHERE Ma_doc_gia = ? AND Ma_sach = ?");
        $check_stmt->execute([$username, $book_id]);
        
        if ($check_stmt->rowCount() == 0) {
            // Thêm vào giỏ
            $insert_stmt = $pdo->prepare("INSERT INTO gio_muon_tam (session_id, Ma_doc_gia, Ma_sach, Ten_sach) VALUES (?, ?, ?, ?)");
            $insert_stmt->execute([session_id(), $username, $book_id, $book['Ten_sach']]);
            
            $_SESSION['success'] = "Đã thêm sách '" . htmlspecialchars($book['Ten_sach']) . "' vào giỏ mượn!";
        } else {
            $_SESSION['error'] = "Sách này đã có trong giỏ mượn!";
        }
    } else {
        $_SESSION['error'] = "Sách không tồn tại hoặc đã hết!";
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit();
?>