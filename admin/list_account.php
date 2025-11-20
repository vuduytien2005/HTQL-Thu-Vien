<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

$stmt = $pdo->query("SELECT id, username, role FROM TAI_KHOAN");
$accounts = $stmt->fetchAll();
?>

<h3>Danh sách tài khoản</h3>
<table border="1">
    <tr><th>ID</th><th>Username</th><th>Role</th><th>Xóa</th></tr>
    <?php foreach ($accounts as $acc): ?>
        <tr>
            <td><?= $acc['id'] ?></td>
            <td><?= $acc['username'] ?></td>
            <td><?= $acc['role'] ?></td>
            <td><a href="delete_account.php?id=<?= $acc['id'] ?>">Xóa</a></td>
        </tr>
    <?php endforeach; ?>
</table>