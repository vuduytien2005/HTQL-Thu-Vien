<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // mã hóa mật khẩu
    $role = 'docgia'; // chỉ cho phép tạo tài khoản độc giả

    // Kiểm tra trùng username
    $stmt = $pdo->prepare("SELECT * FROM TAI_KHOAN WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $error = "❌ Tên đăng nhập đã tồn tại.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO TAI_KHOAN (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);
        $success = "✅ Tạo tài khoản thành công. Bạn có thể đăng nhập.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản độc giả</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>🆕 Đăng ký tài khoản độc giả</h1>
        <?php
        if (!empty($error)) echo "<p style='color:red;'>$error</p>";
        if (!empty($success)) echo "<p style='color:green;'>$success</p>";
        ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit">Đăng ký</button>
        </form>
        <br>
        <a href="login.php" class="button">⬅ Quay về đăng nhập</a>
    </div>
</body>
</html>