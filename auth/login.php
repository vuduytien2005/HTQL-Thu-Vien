<?php
session_start();
require '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = " Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
    } else {
        try {
            // Tìm user trong bảng TAI_KHOAN
            $stmt = $pdo->prepare("SELECT * FROM TAI_KHOAN WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                // Kiểm tra mật khẩu (password_verify cho bcrypt hash)
                if (password_verify($password, $user['password'])) {
                    // Xóa password khỏi session để bảo mật
                    unset($user['password']);
                    
                    // Lưu user vào session
                    $_SESSION['user'] = $user;
                    
                    // KIỂM TRA REDIRECT SAU KHI ĐĂNG NHẬP
                    if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
                        // Giải mã URL đã được encode
                        $redirect_url = urldecode($_GET['redirect_to']);
                        
                        // Kiểm tra xem URL có hợp lệ không (chỉ cho phép các trang trong hệ thống)
                        $allowed_domains = ['../user/', '../docgia/', 'view_book.php', 'cart.php'];
                        $is_valid = false;
                        
                        foreach ($allowed_domains as $domain) {
                            if (strpos($redirect_url, $domain) !== false) {
                                $is_valid = true;
                                break;
                            }
                        }
                        
                        if ($is_valid) {
                            // Thêm tham số id nếu có trong session
                            if (isset($_SESSION['book_id_to_borrow'])) {
                                $book_id = $_SESSION['book_id_to_borrow'];
                                if (strpos($redirect_url, '?') !== false) {
                                    $redirect_url .= "&id=$book_id";
                                } else {
                                    $redirect_url .= "?id=$book_id";
                                }
                                unset($_SESSION['book_id_to_borrow']);
                            }
                            
                            header("Location: $redirect_url");
                            exit();
                        }
                    }
                    
                    // Nếu không có redirect hoặc redirect không hợp lệ, chuyển hướng theo role
                    if ($user['role'] === 'admin') {
                        header('Location: ../admin/dashboard.php');
                        exit();
                    } else {
                        // ĐỘC GIẢ - CHUYỂN VỀ TRANG CHỦ RIÊNG
                        header('Location: ../docgia/index.php');
                        exit();
                    }
                } else {
                    $error = "❌ Mật khẩu không chính xác.";
                }
            } else {
                $error = "❌ Tên đăng nhập không tồn tại.";
            }
        } catch (Exception $e) {
            $error = "❌ Lỗi hệ thống: " . $e->getMessage();
        }
    }
}

// Lấy thông báo từ session nếu có
if (isset($_SESSION['login_message'])) {
    $info_message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Thư viện Sách</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            background-image: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
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
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
            pointer-events: none;
        }
        
        .auth-center {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .auth-title {
            text-align: center;
            margin-bottom: 30px;
            color: #1a2980;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .auth-note {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1a2980;
            box-shadow: 0 0 0 3px rgba(26, 41, 128, 0.1);
            background: white;
        }
        
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(26, 41, 128, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .auth-links {
            margin-top: 25px;
            text-align: center;
        }
        
        .auth-links a {
            color: #1a2980;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 0 10px;
        }
        
        .auth-links a:hover {
            text-decoration: underline;
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .error {
            background-color: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }
        
        .success {
            background-color: #efe;
            color: #090;
            border: 1px solid #cfc;
        }
        
        .info {
            background-color: #e8f4fd;
            color: #0366d6;
            border: 1px solid #b3e0ff;
        }
        
        .role-info {
            margin-top: 20px;
            padding: 15px;
            background: rgba(26, 41, 128, 0.05);
            border-radius: 10px;
            border-left: 4px solid #1a2980;
        }
        
        .role-info h4 {
            color: #1a2980;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        
        .role-info p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #555;
        }
        
        .borrow-notice {
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
        }
        
        .borrow-notice i {
            font-size: 1.2rem;
            margin-right: 8px;
        }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: 30px 20px;
            }
            
            .auth-title {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body class="auth-bg">
    <div class="auth-center">
        <div class="auth-card">
            <h2 class="auth-title">
                <i class="fas fa-book-open"></i>
                Đăng nhập
            </h2>

            <?php if (isset($_SESSION['borrow_redirect'])): ?>
                <div class="borrow-notice">
                    <i class="fas fa-exclamation-triangle"></i>
                    Vui lòng đăng nhập để mượn sách
                </div>
                <?php unset($_SESSION['borrow_redirect']); ?>
            <?php endif; ?>

            <?php if (isset($info_message)): ?>
                <div class="message info">
                    <i class="fas fa-info-circle"></i> 
                    <?php echo htmlspecialchars($info_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Thêm hidden input để giữ lại redirect_to -->
                <?php if (isset($_GET['redirect_to'])): ?>
                    <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_GET['redirect_to']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Tên đăng nhập
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           placeholder="Nhập tên đăng nhập của bạn">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           placeholder="Nhập mật khẩu của bạn">
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Đăng nhập vào hệ thống
                </button>
            </form>

            <div class="auth-links">
                <a href="../public/index.php">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
                <span style="color: #ccc;">|</span>
                <a href="register.php">
                    <i class="fas fa-user-plus"></i> Đăng ký tài khoản
                </a>
            </div>


            <p class="auth-note">
                Sử dụng tài khoản đã được tạo trong hệ thống để đăng nhập
            </p>
        </div>
    </div>
    
    <script>
        // Focus vào ô username khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
            
            // Kiểm tra nếu có thông báo borrow
            const borrowNotice = document.querySelector('.borrow-notice');
            if (borrowNotice) {
                // Tự động ẩn sau 5 giây
                setTimeout(() => {
                    borrowNotice.style.opacity = '0';
                    borrowNotice.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                        borrowNotice.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (username === '' || password === '') {
                e.preventDefault();
                alert('Vui lòng nhập đầy đủ thông tin đăng nhập.');
                return false;
            }
            
            return true;
        });
        
        // Lưu lại redirect_to nếu có
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('redirect_to')) {
            // Giữ lại tham số redirect_to trong form
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect_to';
            redirectInput.value = urlParams.get('redirect_to');
            document.querySelector('form').appendChild(redirectInput);
        }
    </script>
</body>
</html>