<?php
session_start();

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION["user"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION["user"];
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
        <a href="edit_user.php">⚙️ Cập nhật tài khoản</a>
    </div>

    <div class="content">
        <h2>✨ Bảng điều khiển độc giả</h2>
        <p>Chọn chức năng bên dưới để bắt đầu.</p>

        <div class="card-box">
            <h3>📘 Xem danh sách sách</h3>
            <p>Truy cập vào kho sách để xem và đọc thông tin sách.</p>
        </div>

        <div class="card-box">
            <h3>📖 Mượn sách</h3>
            <p>Thực hiện mượn sách nhanh chóng và chính xác.</p>
        </div>

        <div class="card-box">
            <h3>📦 Trả sách</h3>
            <p>Quản lý và hoàn trả sách đã mượn.</p>
        </div>
    </div>

</body>
</html>
