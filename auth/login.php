<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM TAI_KHOAN WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        if ($user['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../docgia/dashboard.php');
        }
    } else {
        $error = "❌ Sai tên đăng nhập hoặc mật khẩu.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <link rel="stylesheet" href="../assets/style.css"> <!-- Đây là dòng cần thêm -->
</head>
<body>
    <div class="container">
        <h1>🔐 Đăng nhập</h1>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Tên đăng nhập" required><br><br>
            <input type="password" name="password" placeholder="Mật khẩu" required><br><br>
            <button type="submit">Đăng nhập</button>
            <a href="register.php" class="button"> Tạo tài khoản độc giả</a>
        </form>
        <br>
        <a href="../index.php" class="button">⬅ Quay về trang chính</a>
        
    </div>
</body>
</html>