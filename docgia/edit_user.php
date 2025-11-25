<?php
session_start();
require '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION["user"])) {
    header('Location: ../auth/login.php');
    exit;
}

// Lấy thông tin user từ session
$user = $_SESSION["user"];
$user_id = $user["id"];
$username = $user["username"];

// Lấy thông tin độc giả từ bảng DOC_GIA dựa vào user_id
$stmt = $pdo->prepare("SELECT * FROM DOC_GIA WHERE Ma_doc_gia = ?");
$stmt->execute([$user_id]);
$doc_gia = $stmt->fetch();

// Nếu chưa có thông tin trong DOC_GIA, tạo bản ghi mới
if (!$doc_gia) {
    // Tạo thông tin mặc định
    $stmt = $pdo->prepare("INSERT INTO DOC_GIA (Ma_doc_gia, Ho_ten) VALUES (?, ?)");
    $stmt->execute([$user_id, $username]);
    
    // Lấy lại thông tin
    $stmt = $pdo->prepare("SELECT * FROM DOC_GIA WHERE Ma_doc_gia = ?");
    $stmt->execute([$user_id]);
    $doc_gia = $stmt->fetch();
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
        $stmt->execute([$ho_ten, $ngay_sinh, $gioi_tinh, $dia_chi, $sdt, $email, $doc_gia['Ma_doc_gia']]);
        
        $success = "✅ Cập nhật thông tin thành công!";
        
        // Cập nhật session
        $_SESSION["user"]["Ho_ten"] = $ho_ten;
        
        // Lấy lại thông tin mới nhất
        $stmt = $pdo->prepare("SELECT * FROM DOC_GIA WHERE Ma_doc_gia = ?");
        $stmt->execute([$doc_gia['Ma_doc_gia']]);
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
    <title>Chỉnh sửa thông tin cá nhân</title>
</head>
<body>
    <div>
        <h1>✏️ Chỉnh sửa thông tin cá nhân</h1>
        
        <?php if (!empty($error)): ?>
            <div><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div>
                <strong>Mã độc giả:</strong>
                <br>
                <input type="text" value="<?php echo htmlspecialchars($doc_gia['Ma_doc_gia']); ?>" disabled>
            </div>

            <div>
                <strong>Tên đăng nhập:</strong>
                <br>
                <input type="text" value="<?php echo htmlspecialchars($username); ?>" disabled>
            </div>
            
            <div>
                <strong>Họ và tên *</strong>
                <br>
                <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($doc_gia['Ho_ten']); ?>" required>
            </div>
            
            <div>
                <strong>Ngày sinh</strong>
                <br>
                <input type="date" name="ngay_sinh" value="<?php echo $doc_gia['Ngay_sinh']; ?>">
            </div>
            
            <div>
                <strong>Giới tính</strong>
                <br>
                <select name="gioi_tinh">
                    <option value="">-- Chọn giới tính --</option>
                    <option value="Nam" <?php echo ($doc_gia['Gioi_tinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                    <option value="Nữ" <?php echo ($doc_gia['Gioi_tinh'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                    <option value="Khác" <?php echo ($doc_gia['Gioi_tinh'] == 'Khác') ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>
            
            <div>
                <strong>Địa chỉ</strong>
                <br>
                <input type="text" name="dia_chi" value="<?php echo htmlspecialchars($doc_gia['Dia_chi']); ?>">
            </div>
            
            <div>
                <strong>Số điện thoại</strong>
                <br>
                <input type="tel" name="sdt" value="<?php echo htmlspecialchars($doc_gia['SDT']); ?>">
            </div>
            
            <div>
                <strong>Email</strong>
                <br>
                <input type="email" name="email" value="<?php echo htmlspecialchars($doc_gia['Email']); ?>">
            </div>
            
            <br>
            <button type="submit">💾 Cập nhật thông tin</button>
        </form>
        
        <br>
        <a href="dashboard.php">⬅ Quay lại trang chủ</a>
    </div>
</body>
</html>