<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền sửa tài khoản.");
}

$id = $_GET['id'] ?? null;
$message = '';

if (!$id) {
    die("Không tìm thấy ID tài khoản.");
}

// Lấy thông tin tài khoản
$stmt = $pdo->prepare("SELECT id, username, role FROM TAI_KHOAN WHERE id = ?");
$stmt->execute([$id]);
$account = $stmt->fetch();

if (!$account) {
    die("Tài khoản không tồn tại.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $new_password = $_POST['new_password'];

    // Kiểm tra username đã tồn tại chưa (trừ tài khoản hiện tại)
    $check_stmt = $pdo->prepare("SELECT id FROM TAI_KHOAN WHERE username = ? AND id != ?");
    $check_stmt->execute([$username, $id]);
    
    if ($check_stmt->rowCount() > 0) {
        $message = "❌ Tên đăng nhập đã tồn tại!";
    } else {
        if (!empty($new_password)) {
            // Cập nhật cả mật khẩu
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE TAI_KHOAN SET username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $hashed_password, $role, $id]);
        } else {
            // Chỉ cập nhật username và role
            $stmt = $pdo->prepare("UPDATE TAI_KHOAN SET username = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $role, $id]);
        }
        
        $message = "✅ Cập nhật tài khoản thành công!";
        
        // Cập nhật lại thông tin tài khoản
        $stmt = $pdo->prepare("SELECT id, username, role FROM TAI_KHOAN WHERE id = ?");
        $stmt->execute([$id]);
        $account = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa tài khoản - Quản trị Thư viện</title>
</head>
<body>
    <h1>Sửa tài khoản</h1>
    
    <?php if (!empty($message)): ?>
        <div style="padding: 10px; margin: 10px 0; border: 1px solid <?php echo strpos($message, '✅') !== false ? '#c3e6cb' : '#f5c6cb'; ?>; background: <?php echo strpos($message, '✅') !== false ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo strpos($message, '✅') !== false ? '#155724' : '#721c24'; ?>;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label>ID:</label>
            <input type="text" value="<?= $account['id'] ?>" disabled>
            <small>(Không thể thay đổi ID)</small>
        </div>

        <div>
            <label>Tên đăng nhập:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($account['username']) ?>" maxlength="50" required>
        </div>

        <div>
            <label>Vai trò:</label>
            <select name="role" required>
                <option value="admin" <?= $account['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="docgia" <?= $account['role'] === 'docgia' ? 'selected' : '' ?>>Độc giả</option>
            </select>
        </div>

        <div>
            <label>Mật khẩu mới (để trống nếu không đổi):</label>
            <input type="password" name="new_password" placeholder="Nhập mật khẩu mới" maxlength="255">
        </div>

        <button type="submit">Cập nhật tài khoản</button>
    </form>

    <br>
    <a href="list_account.php">Quay lại danh sách</a> | 
    <a href="dashboard.php">Quay lại Dashboard</a>
</body>
</html>