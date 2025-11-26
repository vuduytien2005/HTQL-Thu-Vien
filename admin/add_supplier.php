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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm nhà cung cấp</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card-box">
            <h3 style="margin-bottom: 20px; color: var(--primary);">Thêm nhà cung cấp mới</h3>
            
            <form method="post">
                <label for="ten_nha_cung_cap">Tên nhà cung cấp</label>
                <input id="ten_nha_cung_cap" type="text" name="ten_nha_cung_cap" required>
                
                <div style="margin-top:16px;">
                    <button class="btn btn-primary" type="submit">Thêm nhà cung cấp</button>
                    <a class="back-link" href="add_book.php" style="margin-left:12px;">Quay lại thêm sách</a>
                </div>
            </form>
        </div>
        
        <div style="margin-top:16px;">
            <a class="back-link" href="dashboard.php">← Quay về Dashboard</a>
        </div>
    </div>
</body>
</html>