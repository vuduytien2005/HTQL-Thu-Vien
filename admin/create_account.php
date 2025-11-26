<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền tạo tài khoản.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("INSERT INTO TAI_KHOAN (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $role]);

    // Chuyển hướng về dashboard với thông báo thành công
    header('Location: dashboard.php?success=Tạo tài khoản thành công');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo tài khoản mới</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card-box">
            <h3 style="margin-bottom: 20px; color: var(--primary);">Tạo tài khoản mới</h3>

            <form method="POST">
                <label for="username">Tên đăng nhập</label>
                <input id="username" type="text" name="username" placeholder="Tên đăng nhập" required>

                <label for="password">Mật khẩu</label>
                <input id="password" type="password" name="password" placeholder="Mật khẩu" required>

                <label for="role">Vai trò</label>
                <select id="role" name="role">
                    <option value="admin">Admin</option>
                    <option value="docgia">Độc giả</option>
                </select>

                <div style="margin-top:16px;">
                    <button class="btn btn-primary" type="submit">Tạo tài khoản</button>
                    <a class="back-link" href="dashboard.php" style="margin-left:12px;">Hủy</a>
                </div>
            </form>
        </div>

        <div style="margin-top:16px;">
            <a class="back-link" href="dashboard.php">← Quay về Dashboard</a>
        </div>
    </div>
</body>
</html>