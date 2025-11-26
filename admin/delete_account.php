<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền xóa tài khoản.");
}

$id = $_GET['id'] ?? null;
$message = '';

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM TAI_KHOAN WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: list_account.php?success=Đã xóa tài khoản thành công');
        exit();
    } catch (Exception $e) {
        $message = "❌ Lỗi khi xóa tài khoản: " . $e->getMessage();
    }
} else {
    $message = "❌ Không tìm thấy ID tài khoản.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xóa tài khoản</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card-box">
            <h3 style="margin-bottom: 20px; color: var(--primary);">Xóa tài khoản</h3>

            <?php if (!empty($message)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div style="margin-top:16px;">
                <a class="back-link" href="list_account.php">← Quay lại danh sách tài khoản</a>
            </div>
        </div>
    </div>
</body>
</html>