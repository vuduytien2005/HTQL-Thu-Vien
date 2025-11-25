<?php
include '../config/db.php'; // Kết nối PDO

// Kiểm tra tham số truyền vào
if (!isset($_GET['Ma_doc_gia'])) {
    die("❌ Không tìm thấy mã độc giả để xoá!");
}

$ma_doc_gia = $_GET['Ma_doc_gia'];

try {

    // Chuẩn bị câu lệnh xoá
    $stmt = $pdo->prepare("DELETE FROM doc_gia WHERE Ma_doc_gia = :id");

    $stmt->execute([
        ':id' => $ma_doc_gia
    ]);

    // Chuyển về danh sách sau khi xoá
    header("Location: list_user.php?msg=deleted");
    exit;

} catch (Exception $e) {
    die("❌ Lỗi khi xoá độc giả: " . $e->getMessage());
}
?>
