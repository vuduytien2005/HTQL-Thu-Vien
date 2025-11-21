<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền tạo tài khoản.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Kiểm tra username đã tồn tại chưa
    $check_stmt = $pdo->prepare("SELECT id FROM TAI_KHOAN WHERE username = ?");
    $check_stmt->execute([$username]);
    
    if ($check_stmt->rowCount() > 0) {
        $message = "❌ Tên đăng nhập đã tồn tại!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Thêm vào database
        $stmt = $pdo->prepare("INSERT INTO TAI_KHOAN (username, password, role) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$username, $hashed_password, $role])) {
            $message = "✅ Tạo tài khoản thành công!";
        } else {
            $message = "❌ Lỗi khi tạo tài khoản!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo tài khoản mới - Quản trị Thư viện</title>
</head>
<body>
    
    <form method="POST">
        <h2>Tạo tài khoản mới</h2>
        
        <?php if (!empty($message)): ?>
            <div style="padding: 10px; margin: 10px 0; border: 1px solid <?php echo strpos($message, '✅') !== false ? '#c3e6cb' : '#f5c6cb'; ?>; background: <?php echo strpos($message, '✅') !== false ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo strpos($message, '✅') !== false ? '#155724' : '#721c24'; ?>;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div>
            <label>Tên đăng nhập:</label>
            <input type="text" name="username" placeholder="Tên đăng nhập" maxlength="50" required>
        </div>

        <div>
            <label>Mật khẩu:</label>
            <input type="password" name="password" placeholder="Mật khẩu" maxlength="255" required>
        </div>

        <div>
            <label>Vai trò:</label>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="docgia">Độc giả</option>
            </select>
        </div>

        <button type="submit">Tạo tài khoản</button>
    </form>

    <a href="dashboard.php">Quay lại Dashboard</a>
</body>
</html>