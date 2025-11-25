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

<h3>Tạo tài khoản mới</h3>

<!-- Thêm nút quay về Dashboard -->
<p>
    <a href="dashboard.php">← Quay về Dashboard</a>
</p>

<form method="POST">
    <p>
        <label>Tên đăng nhập:</label><br>
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
    </p>
    <p>
        <label>Mật khẩu:</label><br>
        <input type="password" name="password" placeholder="Mật khẩu" required>
    </p>
    <p>
        <label>Vai trò:</label><br>
        <select name="role">
            <option value="admin">Admin</option>
            <option value="docgia">Độc giả</option>
        </select>
    </p>
    <p>
        <button type="submit">Tạo tài khoản</button>
        <a href="dashboard.php">Hủy</a>
    </p>
</form>