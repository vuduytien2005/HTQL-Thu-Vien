<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'docgia';

    // Thông tin độc giả từ form
    $ho_ten = $_POST['ho_ten'];
    $ngay_sinh = !empty($_POST['ngay_sinh']) ? $_POST['ngay_sinh'] : null;
    $gioi_tinh = !empty($_POST['gioi_tinh']) ? $_POST['gioi_tinh'] : null;
    $dia_chi = !empty($_POST['dia_chi']) ? $_POST['dia_chi'] : null;
    $sdt = !empty($_POST['sdt']) ? $_POST['sdt'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;

    try {
        $pdo->beginTransaction();

        // Kiểm tra trùng username
        $stmt = $pdo->prepare("SELECT * FROM TAI_KHOAN WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "❌ Tên đăng nhập đã tồn tại.";
        } else {
            // Tạo tài khoản mới
            $stmt = $pdo->prepare("INSERT INTO TAI_KHOAN (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password, $role]);
            
            // Lấy ID tài khoản vừa tạo
            $account_id = $pdo->lastInsertId();
            
            // Tạo mã độc giả
            $ma_doc_gia = 'DG' . str_pad($account_id, 6, '0', STR_PAD_LEFT);
            
            // Thêm thông tin vào bảng DOC_GIA
            $stmt = $pdo->prepare("INSERT INTO DOC_GIA (Ma_doc_gia, Ho_ten, Ngay_sinh, Gioi_tinh, Dia_chi, SDT, Email) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ma_doc_gia, $ho_ten, $ngay_sinh, $gioi_tinh, $dia_chi, $sdt, $email]);
            
            $pdo->commit();
            $success = "✅ Tạo tài khoản thành công. Bạn có thể đăng nhập.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "❌ Có lỗi xảy ra khi tạo tài khoản: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản độc giả</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>🆕 Đăng ký tài khoản độc giả</h1>
        
        <?php
        if (!empty($error)) echo "<p style='color:red;'>$error</p>";
        if (!empty($success)) echo "<p style='color:green;'>$success</p>";
        ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Tên đăng nhập" required><br><br>
            
            <input type="password" name="password" placeholder="Mật khẩu" required><br><br>
            
            <input type="text" name="ho_ten" placeholder="Họ và tên" required><br><br>
            
            <!-- Dòng Ngày sinh -->
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <strong style="width: 100px;">Ngày sinh</strong>
                <input type="date" name="ngay_sinh">
            </div>
            
            <!-- Dòng Giới tính -->
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <strong style="width: 100px;">Giới tính</strong>
                <select name="gioi_tinh">
                    <option value="">-- Chọn giới tính --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>
            
            <input type="text" name="dia_chi" placeholder="Địa chỉ"><br><br>
            
            <input type="tel" name="sdt" placeholder="Số điện thoại"><br><br>
            
            <input type="email" name="email" placeholder="Email">
            
            <br><br>
            <button type="submit">Đăng ký</button>
        </form>
        
        <br>
        <a href="login.php" class="button">⬅ Quay về đăng nhập</a>
    </div>
</body>
</html>