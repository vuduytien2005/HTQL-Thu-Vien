<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

// Xử lý cập nhật tài khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE TAI_KHOAN SET username = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $role, $hashed_password, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE TAI_KHOAN SET username = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $role, $id]);
    }
    
    header('Location: list_account.php?success=Cập nhật tài khoản thành công');
    exit();
}

// Xử lý hiển thị form sửa
$edit_account = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT id, username, role FROM TAI_KHOAN WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $edit_account = $stmt->fetch();
}

$stmt = $pdo->query("SELECT id, username, role FROM TAI_KHOAN");
$accounts = $stmt->fetchAll();
?>

<h3>Danh sách tài khoản</h3>

<!-- Thông báo -->
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo $_GET['success']; ?></p>
<?php endif; ?>

<!-- Nút quay về Dashboard -->
<p>
    <a href="/QLY_THUVIEN/HTQL-Thu-Vien/admin/dashboard.php">← Quay về Dashboard</a>
</p>

<!-- Form sửa tài khoản (chỉ hiện khi đang sửa) -->
<?php if ($edit_account): ?>
    <h4>Sửa tài khoản: <?= $edit_account['username'] ?></h4>
    <form method="post" action="list_account.php">
        <input type="hidden" name="id" value="<?= $edit_account['id'] ?>">
        <input type="hidden" name="update_account" value="1">
        
        <p>
            <label>Username:</label><br>
            <input type="text" name="username" value="<?= $edit_account['username'] ?>" required>
        </p>
        
        <p>
            <label>Role:</label><br>
            <select name="role">
                <option value="user" <?= $edit_account['role'] == 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $edit_account['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </p>
        
        <p>
            <label>Mật khẩu mới (để trống nếu không đổi):</label><br>
            <input type="password" name="password">
        </p>
        
        <p>
            <button type="submit">Cập nhật</button>
            <a href="list_account.php">Hủy</a>
        </p>
    </form>
    <hr>
<?php endif; ?>

<!-- Bảng danh sách tài khoản -->
<table border="1">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Thao tác</th>
    </tr>
    <?php foreach ($accounts as $acc): ?>
        <tr>
            <td><?= $acc['id'] ?></td>
            <td><?= $acc['username'] ?></td>
            <td><?= $acc['role'] ?></td>
            <td>
                <a href="list_account.php?edit_id=<?= $acc['id'] ?>">Sửa</a>
                <a href="delete_account.php?id=<?= $acc['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?')">Xóa</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>