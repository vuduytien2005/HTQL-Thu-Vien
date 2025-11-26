<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Quản lý Thư viện</title>
    <link rel="stylesheet" href="assets/style.css">
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
        
        body {
            font-family: Inter, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-image: url('https://4kwallpapers.com/images/walls/thumbs_3t/7059.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        body::before {
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
        
        .container {
            position: relative;
            z-index: 2;
            max-width: 700px;
            width: 100%;
            padding: 48px 40px;
            text-align: center;
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 16px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .button-group {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .button {
            display: inline-block;
            padding: 16px 32px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            min-width: 200px;
        }
        
        .button:first-child {
            background: #ffffff;
            color: #106db1;
        }
        
        .button:first-child:hover {
            background: #f0f9ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }
        
        .button:last-child {
            background: #0ea5a9;
            color: #ffffff;
            border: 2px solid #ffffff;
        }
        
        .button:last-child:hover {
            background: #0d8b8f;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }
        
        @media (max-width: 640px) {
            h1 {
                font-size: 1.75rem;
            }
            
            p {
                font-size: 1rem;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hệ thống Quản lý Thư viện</h1>
        <p>Chào mừng đến với hệ thống quản lý thư viện. Vui lòng chọn chức năng bên dưới:</p>
        <div class="button-group">
            <a href="auth/login.php" class="button"> Đăng nhập</a>
            <a href="auth/register.php" class="button"> Tạo tài khoản</a>
        </div>
    </div>
</body>
</html>