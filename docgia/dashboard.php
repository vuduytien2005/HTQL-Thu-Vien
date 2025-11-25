<?php
session_start();
require '../config/db.php';

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION["user"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION["user"];
$ma_doc_gia = $_SESSION['Ma_doc_gia'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>📚 Bảng điều khiển độc giả</title>
</head>
<body>

    <div class="header">
        <h2>📚 Xin chào, <?php echo htmlspecialchars($user["username"]); ?>!</h2>
        <a href="../auth/logout.php" style="color:white; text-decoration:underline;">Đăng xuất</a>
    </div>

    <div class="menu">
        <a href="giaodien.php">📘 Xem sách</a>
        <a href="borrow_book.php">📖 Mượn sách</a>
        <a href="return_book.php">📦 Trả sách</a>
        <a href="search_book.php">📦 Tìm kiếm sách</a>
        <a href="edit_user.php?id=<?php echo htmlspecialchars($ma_doc_gia); ?>">⚙️ Cập nhật tài khoản</a>
    </div>
    </div>

</body>
</html>
