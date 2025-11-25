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
        $stmt = $pdo->prepare("INSERT INTO sach_the_loai (Ma_the_loai, Ten_the_loai, Mo_ta) VALUES (?, ?, ?)");
        $stmt->execute([$ma_the_loai, $ten_the_loai, $mo_ta]);
        
        header('Location: add_book.php?success=Thêm thể loại thành công');
        exit();
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<h3>Thêm thể loại mới</h3>

<!-- Thông báo lỗi -->
<?php if (isset($error)): ?>
    <p style="color: red;">
        <?php 
        if (strpos($error, 'Duplicate entry') !== false) {
            echo "Mã thể loại đã tồn tại. Xin vui lòng nhập mã khác.";
        } else {
            echo $error;
        }
        ?>
    </p>
<?php endif; ?>

<form method="post">
    <p>
        <label>Mã thể loại:</label><br>
        <input type="text" name="ma_the_loai" placeholder="VD: TL01, TL02..." required>
    </p>
    <p>
        <label>Tên thể loại:</label><br>
        <input type="text" name="ten_the_loai" placeholder="VD: Tiểu thuyết, Khoa học..." required>
    </p>
    <p>
        <label>Mô tả:</label><br>
        <textarea name="mo_ta" placeholder="Mô tả về thể loại..." rows="4" style="width: 300px;"></textarea>
    </p>
    <p>
        <button type="submit">Thêm thể loại</button>
        <a href="add_book.php">Quay lại thêm sách</a>
    </p>
</form>

<!-- Nút quay về Dashboard -->
<p>
    <a href="dashboard.php">← Quay về Dashboard</a>
</p>