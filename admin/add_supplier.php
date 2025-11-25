<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_nha_cung_cap = $_POST['ten_nha_cung_cap'];
    
    $stmt = $pdo->prepare("INSERT INTO nha_cung_cap (Ten_nha_cung_cap) VALUES (?)");
    $stmt->execute([$ten_nha_cung_cap]);
    
    header('Location: add_book.php?success=Thêm nhà cung cấp thành công');
    exit();
}
?>

<h3>Thêm nhà cung cấp mới</h3>
<form method="post">
    <p>
        <label>Tên nhà cung cấp:</label><br>
        <input type="text" name="ten_nha_cung_cap" required>
    </p>
    <p>
        <button type="submit">Thêm nhà cung cấp</button>
        <a href="add_book.php">Quay lại thêm sách</a>
    </p>
</form>