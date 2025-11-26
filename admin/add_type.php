<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_the_loai = $_POST['ma_the_loai'];
    $ten_the_loai = $_POST['ten_the_loai'];
    $mo_ta = $_POST['mo_ta'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO the_loai (Ma_the_loai, Ten_the_loai, Mo_ta) VALUES (?, ?, ?)");
        $stmt->execute([$ma_the_loai, $ten_the_loai, $mo_ta]);
        
        header('Location: add_book.php?success=Thêm thể loại thành công');
        exit();
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm thể loại</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card-box">
            <h3 style="margin-bottom: 20px; color: var(--primary);">Thêm thể loại mới</h3>

            <!-- Thông báo lỗi -->
            <?php if (isset($error)): ?>
                <div class="message error">
                    <?php 
                    if (strpos($error, 'Duplicate entry') !== false) {
                        echo "❌ Mã thể loại đã tồn tại. Xin vui lòng nhập mã khác.";
                    } else {
                        echo htmlspecialchars($error);
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <label for="ma_the_loai">Mã thể loại</label>
                <input id="ma_the_loai" type="text" name="ma_the_loai" placeholder="VD: TL01, TL02..." required>

                <label for="ten_the_loai">Tên thể loại</label>
                <input id="ten_the_loai" type="text" name="ten_the_loai" placeholder="VD: Tiểu thuyết, Khoa học..." required>

                <label for="mo_ta">Mô tả</label>
                <textarea id="mo_ta" name="mo_ta" placeholder="Mô tả về thể loại..." rows="4" style="width: 100%; max-width: 520px; padding: 12px; border-radius: 8px; border: 1px solid #e6eef8;"></textarea>

                <div style="margin-top:16px;">
                    <button class="btn btn-primary" type="submit">Thêm thể loại</button>
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