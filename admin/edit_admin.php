<?php
// edit_admin.php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

// Lấy ID admin đang đăng nhập
$admin_id = $_SESSION['user']['id'];

// Lấy thông tin admin từ database
$stmt = $pdo->prepare("SELECT * FROM tai_khoan WHERE id = ? AND role = 'admin'");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) {
    die("Không tìm thấy quản trị viên.");
}

$error = '';
$success = '';

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Kiểm tra đơn giản
    if (empty($new_password)) {
        $error = 'Vui lòng nhập mật khẩu mới!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Mật khẩu mới và xác nhận mật khẩu không khớp!';
    } else {
        try {
            // Hash mật khẩu và lưu vào database
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE tai_khoan SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $admin_id]);
            
            $success = 'Đổi mật khẩu thành công!';
            
            // Reset form fields
            $_POST['new_password'] = '';
            $_POST['confirm_password'] = '';
            
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .card-box {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
        }
        
        h3 {
            margin-bottom: 20px;
            color: var(--primary);
            font-size: 1.2rem;
            text-align: center;
        }
        
        .message {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: 500;
            text-align: center;
        }
        
        .message.success {
            background: #efe;
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .message.error {
            background: #fee;
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text);
            font-size: 0.9rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e6eef8;
            border-radius: 5px;
            font-size: 0.95rem;
            background: #fff;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn {
            padding: 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: 0.3s;
            width: 100%;
            display: block;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            margin-bottom: 10px;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .admin-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .admin-info p {
            margin: 5px 0;
            color: var(--text);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card-box">
            <h3>🔐 Đổi mật khẩu</h3>
            
            <!-- Thông báo -->
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Thông tin tài khoản -->
            <div class="admin-info">
                <p>Tài khoản: <strong><?php echo htmlspecialchars($admin['username']); ?></strong></p>
                <p>Vai trò: <strong><?php echo htmlspecialchars($admin['role']); ?></strong></p>
            </div>
            
            <!-- Form đổi mật khẩu -->
            <form method="POST">
                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password" 
                           value="<?php echo isset($_POST['new_password']) ? htmlspecialchars($_POST['new_password']) : ''; ?>" 
                           required 
                           placeholder="Nhập mật khẩu mới">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           value="<?php echo isset($_POST['confirm_password']) ? htmlspecialchars($_POST['confirm_password']) : ''; ?>" 
                           required 
                           placeholder="Nhập lại mật khẩu">
                </div>
                
                <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                <a href="dashboard.php" class="btn btn-secondary">Quay lại</a>
            </form>
        </div>
    </div>
    
    <script>
        // Kiểm tra mật khẩu khớp
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                alert('Mật khẩu không khớp!');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>