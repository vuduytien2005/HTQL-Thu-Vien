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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            height: 100%;
        }
        
        body.auth-bg {
            background-image: url('https://4kwallpapers.com/images/walls/thumbs_3t/911.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        body.auth-bg::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
            pointer-events: none;
        }
        
        .auth-center {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body class="auth-bg">
    <div class="auth-center">
        <div class="auth-card">
            <h2 class="auth-title">🆕 Đăng ký tài khoản độc giả</h2>
            
            <?php
            if (!empty($error)) echo "<div class='message error'>" . htmlspecialchars($error) . "</div>";
            if (!empty($success)) echo "<div class='message success'>" . htmlspecialchars($success) . "</div>";
            ?>
            
            <form method="POST">
                <label for="username">Tên đăng nhập</label>
                <input id="username" type="text" name="username" required>
                
                <label for="password">Mật khẩu</label>
                <input id="password" type="password" name="password" required>
                
                <label for="ho_ten">Họ và tên</label>
                <input id="ho_ten" type="text" name="ho_ten" required>
                
                <label for="ngay_sinh">Ngày sinh</label>
                <input id="ngay_sinh" type="date" name="ngay_sinh">
                
                <label for="gioi_tinh">Giới tính</label>
                <select id="gioi_tinh" name="gioi_tinh">
                    <option value="">-- Chọn giới tính --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                </select>
                
                <label for="dia_chi">Địa chỉ</label>
                <input id="dia_chi" type="text" name="dia_chi">
                
                <label for="sdt">Số điện thoại</label>
                <input id="sdt" type="tel" name="sdt">
                
                <label for="email">Email</label>
                <input id="email" type="email" name="email">
                
                <div style="margin-top:16px;">
                    <button class="btn btn-primary" type="submit">Đăng ký</button>
                    <a class="back-link" href="login.php" style="margin-left:12px;">⬅ Quay về đăng nhập</a>
                </div>
            </form>

            <p class="auth-note">Tạo tài khoản để sử dụng dịch vụ thư viện.</p>
        </div>
    </div>

</body>
</html>