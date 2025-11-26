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
        $errorMessage = "❌ Sai tên đăng nhập hoặc mật khẩu.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            height: 100%;
        }
        
        body.auth-bg {
            background-image: url('https://4kwallpapers.com/images/walls/thumbs_3t/14889.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        body.auth-bg::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
            pointer-events: none;
        }
        
        .auth-center {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body class="auth-bg">
    <div class="auth-center">
        <div class="auth-card">
            <h2 class="auth-title">Đăng nhập</h2>

            <!-- Hiển thị thông báo lỗi nếu có -->
            <?php if (!empty($errorMessage)): ?>
                <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="username">Tên đăng nhập</label>
                <input id="username" type="text" name="username" required>

                <label for="password">Mật khẩu</label>
                <input id="password" type="password" name="password" required>

                <div style="margin-top:16px;">
                    <button class="btn btn-primary" type="submit">Đăng nhập</button>
                    <a class="back-link" href="../index.php" style="margin-left:12px;">Quay lại trang chủ</a>
                </div>
            </form>

            <!-- Optional small note -->
            <p class="auth-note">Nhập thông tin của bạn để truy cập hệ thống.</p>
        </div>
    </div>

</body>
</html>