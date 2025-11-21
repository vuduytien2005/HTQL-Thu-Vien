<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

$stmt = $pdo->query("SELECT id, username, role FROM TAI_KHOAN");
$accounts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách tài khoản - Quản trị Thư viện</title>
</head>
<body>
    <h1>Danh sách tài khoản</h1>
    
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Sửa</th>
            <th>Xóa</th>
        </tr>
        <?php foreach ($accounts as $acc): ?>
            <tr>
                <td><?= $acc['id'] ?></td>
                <td><?= $acc['username'] ?></td>
                <td><?= $acc['role'] ?></td>
                <td><a href="edit_account.php?id=<?= $acc['id'] ?>">Sửa</a></td>
                <td><a href="delete_account.php?id=<?= $acc['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa tài khoản này?')">Xóa</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <br>
    <a href="create_account.php">Tạo tài khoản mới</a> | 
    <a href="dashboard.php">Quay lại Dashboard</a>
</body>
</html>