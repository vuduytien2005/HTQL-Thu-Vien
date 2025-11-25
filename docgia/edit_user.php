<?php
session_start();
require '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Lấy mã độc giả từ URL
$ma_doc_gia = $_GET['id'] ?? '';

if (!$ma_doc_gia) {
    die("❌ Không tìm thấy độc giả");
}

// Lấy thông tin độc giả hiện tại
$stmt = $pdo->prepare("SELECT * FROM DOC_GIA WHERE Ma_doc_gia = ?");
$stmt->execute([$ma_doc_gia]);
$doc_gia = $stmt->fetch();

if (!$doc_gia) {
    die("❌ Không tìm thấy thông tin độc giả");
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = $_POST['ho_ten'];
    $ngay_sinh = !empty($_POST['ngay_sinh']) ? $_POST['ngay_sinh'] : null;
    $gioi_tinh = !empty($_POST['gioi_tinh']) ? $_POST['gioi_tinh'] : null;
    $dia_chi = !empty($_POST['dia_chi']) ? $_POST['dia_chi'] : null;
    $sdt = !empty($_POST['sdt']) ? $_POST['sdt'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;

    try {
        $stmt = $pdo->prepare("UPDATE DOC_GIA SET Ho_ten = ?, Ngay_sinh = ?, Gioi_tinh = ?, Dia_chi = ?, SDT = ?, Email = ? WHERE Ma_doc_gia = ?");
        $stmt->execute([$ho_ten, $ngay_sinh, $gioi_tinh, $dia_chi, $sdt, $email, $ma_doc_gia]);
        
        $success = "✅ Cập nhật thông tin độc giả thành công!";
        
        // Lấy lại thông tin mới nhất
        $stmt = $pdo->prepare("SELECT * FROM DOC_GIA WHERE Ma_doc_gia = ?");
        $stmt->execute([$ma_doc_gia]);
        $doc_gia = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = "❌ Có lỗi xảy ra: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa thông tin độc giả</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>✏️ Chỉnh sửa thông tin độc giả</h1>
        
        <?php
        if (!empty($error)) echo "<p style='color:red;'>$error</p>";
        if (!empty($success)) echo "<p style='color:green;'>$success</p>";
        ?>
        
        <form method="POST">
            <!-- Hiển thị mã độc giả (không cho sửa) -->
            <div style="margin-bottom: 15px;">
                <strong>Mã độc giả:</strong>
                <br>
                <input type="text" value="<?php echo htmlspecialchars($doc_gia['Ma_doc_gia']); ?>" disabled style="background-color: #f0f0f0;">
            </div>
            
            <p><strong>Họ và tên *</strong></p>
            <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($doc_gia['Ho_ten']); ?>" placeholder="Họ và tên" required>
            
            <!-- Dòng Ngày sinh -->
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <strong style="width: 100px;">Ngày sinh</strong>
                <input type="date" name="ngay_sinh" value="<?php echo $doc_gia['Ngay_sinh']; ?>">
            </div>
            
            <!-- Dòng Giới tính -->
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <strong style="width: 100px;">Giới tính</strong>
                <select name="gioi_tinh">
                    <option value="">-- Chọn giới tính --</option>
                    <option value="Nam" <?php echo ($doc_gia['Gioi_tinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                    <option value="Nữ" <?php echo ($doc_gia['Gioi_tinh'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                    <option value="Khác" <?php echo ($doc_gia['Gioi_tinh'] == 'Khác') ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>
            
            <p><strong>Địa chỉ</strong></p>
            <input type="text" name="dia_chi" value="<?php echo htmlspecialchars($doc_gia['Dia_chi']); ?>" placeholder="Địa chỉ">
            
            <p><strong>Số điện thoại</strong></p>
            <input type="tel" name="sdt" value="<?php echo htmlspecialchars($doc_gia['SDT']); ?>" placeholder="Số điện thoại">
            
            <p><strong>Email</strong></p>
            <input type="email" name="email" value="<?php echo htmlspecialchars($doc_gia['Email']); ?>" placeholder="Email">
            
            <br><br>
            <button type="submit">💾 Cập nhật thông tin</button>
        </form>
        
        <br>
        <a href="javascript:history.back()" class="button">⬅ Quay lại</a>
    </div>
</body>
</html>