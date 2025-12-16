<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

$current_admin = $_SESSION['user']['username'] ?? 'Quản trị viên';

// Lấy thông tin admin từ database nếu có
$stmt = $pdo->prepare("SELECT * FROM tai_khoan WHERE username = ?");
$stmt->execute([$current_admin]);
$admin_info = $stmt->fetch();

// Hàm lấy chữ cái đầu của tên để làm avatar
function getAvatarInitials($name) {
    if (empty($name)) return 'AD';
    
    $names = explode(' ', $name);
    $initials = '';
    
    if (count($names) >= 2) {
        $initials = strtoupper(substr($names[0], 0, 1) . substr($names[count($names)-1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }
    
    return $initials;
}

$avatarInitials = getAvatarInitials($current_admin);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị Thư viện - Bảng điều khiển Admin</title>
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
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            color: #333;
            line-height: 1.6;
        }
        
        /* Header & Navigation */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a2980;
            text-decoration: none;
        }
        
        .logo i {
            color: #26d0ce;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #444;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }
        
        .nav-links a:hover {
            color: #1a2980;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #26d0ce;
            transition: width 0.3s;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        /* User Avatar & Dropdown */
        .user-avatar {
            position: relative;
            cursor: pointer;
        }
        
        .avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.3s;
            border: 3px solid transparent;
            overflow: hidden;
        }
        
        .avatar-circle:hover {
            transform: scale(1.05);
            border-color: #26d0ce;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .avatar-circle .avatar-initials {
            text-transform: uppercase;
        }
        
        .user-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 250px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1001;
            overflow: hidden;
        }
        
        .user-avatar:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-info {
            padding: 20px;
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .user-details h4 {
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .user-details p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .dropdown-menu {
            list-style: none;
            padding: 10px 0;
        }
        
        .dropdown-menu li {
            border-bottom: 1px solid #f0f0f0;
        }
        
        .dropdown-menu li:last-child {
            border-bottom: none;
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            color: #555;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .dropdown-menu a:hover {
            background-color: #f8f9fa;
            color: #1a2980;
            padding-left: 25px;
        }
        
        .dropdown-menu a i {
            width: 20px;
            color: #26d0ce;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #f0f0f0;
            margin: 5px 0;
        }
        
        /* Hero Section */
        .hero {
            padding: 150px 20px 80px;
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.8)), 
                        url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            text-align: center;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3.2rem;
            margin-bottom: 20px;
            color: #1a2980;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.3rem;
            color: #555;
            margin-bottom: 30px;
        }
        
        /* Stats Section */
        .stats-section {
            padding: 80px 20px;
            background-color: #f8f9fa;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: #1a2980;
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 12px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-left: 5px solid #26d0ce;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card.overdue {
            border-left-color: #ff6b6b;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 25px;
            display: block;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #1a2980;
            margin: 10px 0;
        }
        
        .stat-card.overdue .stat-number {
            color: #ff6b6b;
        }
        
        .stat-card p {
            color: #666;
            font-size: 1.1rem;
        }
        
        /* Quick Actions Section */
        .quick-actions-section {
            padding: 80px 20px;
            background-color: white;
        }
        
        .actions-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .action-card {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            border-color: #26d0ce;
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 25px;
            display: block;
            color: #1a2980;
        }
        
        .action-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #1a2980;
        }
        
        .action-card p {
            color: #666;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .action-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(26, 41, 128, 0.3);
            border-color: white;
        }
        
        /* Recent Books Section - Sửa theo list_book */
        .books-section {
            padding: 80px 20px;
            background-color: #f8f9fa;
        }
        
        .books-management {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .page-title {
            color: #1a2980;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            background: #fbfdff;
            color: #666;
            font-weight: 700;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eef2f7;
            text-align: left;
        }
        
        .book-title {
            font-weight: 600;
            color: #333;
        }
        
        .book-author {
            color: #666;
            font-size: 0.9rem;
            margin-top: 2px;
        }
        
        .book-content {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #666;
            font-size: 0.85rem;
        }
        
        .book-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-available {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .status-unavailable {
            background: #fecaca;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .categories-list {
            font-size: 0.85rem;
            color: #666;
            margin-top: 4px;
        }
        
        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            border: none;
            cursor: pointer;
        }
        
        .btn-edit {
            background: #0d6efd;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-edit:hover {
            background: #0d5a9d;
            text-decoration: none;
        }
        
        .btn-delete:hover {
            background: #c81e4a;
            text-decoration: none;
        }
        
        /* Reports Section */
        .reports-section {
            padding: 80px 20px;
            background-color: white;
        }
        
        .reports-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .reports-table th {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            background: #f8f9fa;
        }
        
        .reports-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #eef2f7;
        }
        
        .reports-table a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }
        
        .reports-table a:hover {
            text-decoration: underline;
        }
        
        /* Footer */
        footer {
            background-color: #0c1127;
            color: #aaa;
            padding: 60px 20px 30px;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        
        .footer-column h3 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #26d0ce;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: #26d0ce;
        }
        
        .copyright {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #777;
            font-size: 0.9rem;
        }
        
        /* Message Styles */
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                order: 2;
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }
            
            .user-dropdown {
                position: fixed;
                top: 80px;
                right: 20px;
                left: 20px;
                width: auto;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .stats-container,
            .actions-container {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
        }
        
        @media (max-width: 576px) {
            .hero {
                padding: 130px 15px 60px;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .stat-card,
            .action-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="nav-container">
            <a href="dashboard.php" class="logo">         
                <span>Thư viện Sách</span>
            </a>
            
            <div class="nav-links">
                <a href="#books">Sách gần đây</a>
                <a href="#reports">Báo cáo</a>
            </div>
            
            <!-- User Avatar -->
            <div class="user-avatar">
                <div class="avatar-circle">
                    <span class="avatar-initials"><?php echo $avatarInitials; ?></span>
                </div>
                
                <div class="user-dropdown">
                    <div class="user-info">
                        <div class="user-info-avatar">
                            <span><?php echo $avatarInitials; ?></span>
                        </div>
                        <div class="user-details">
                            <h4><?php echo htmlspecialchars($current_admin); ?></h4>
                            <p>Quản trị viên</p>
                        </div>
                    </div>
                    
                    <ul class="dropdown-menu">
                        <li><a href="add_book.php"> Thêm sách</a></li>
                        <li><a href="list_book.php"> Quản lý sách</a></li>
                        <li><a href="add_type.php"> Thêm thể loại</a></li>
                        <li><a href="add_supplier.php"> Thêm nhà cung cấp</a></li>
                        <li><a href="list_account.php"> Quản lý số lượng tài khoản</a></li>
                        <li><a href="edit_admin.php"> Đổi mật khẩu</a></li>
                        <li><a href="create_account.php"> Tạo tài khoản mới</a></li>
                        <div class="dropdown-divider"></div>
                        <li><a href="../auth/logout.php"> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Chào mừng đến Bảng điều khiển Quản trị!</h1>
            <p>Quản lý toàn bộ hệ thống thư viện một cách dễ dàng và hiệu quả</p>
        </div>
    </section>

    <!-- Success Message -->
    <?php if (isset($_GET['success'])): ?>
        <div class="message success" style="max-width: 1200px; margin: 20px auto; padding: 20px;">
            ✅ <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>


    <!-- Recent Books Section -->
    <section class="books-section" id="books">
        <div class="section-title">
            <h2>Sách mới thêm gần đây</h2>
            <p>5 cuốn sách được thêm mới nhất vào hệ thống</p>
            <a href="list_book.php" style="color: #1a2980; text-decoration: none; font-weight: 600; margin-top: 10px; display: inline-block;">
                Xem tất cả sách →
            </a>
        </div>
        
        <div id="recent-books">
            <div style="text-align: center; padding: 40px 20px; color: #666;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #ccc; margin-bottom: 15px;"></i>
                <p>Đang tải dữ liệu sách...</p>
            </div>
        </div>
    </section>

    <!-- Reports Section -->
    <section class="reports-section" id="reports">
        <div class="section-title">
            <h2>Báo cáo gần đây</h2>
            <p>5 báo cáo mới nhất trong hệ thống</p>
            <a href="list_report.php" style="color: #1a2980; text-decoration: none; font-weight: 600; margin-top: 10px; display: inline-block;">
                Xem tất cả báo cáo →
            </a>
        </div>
        
        <div id="recent-reports">
            <div style="text-align: center; padding: 40px 20px; color: #666;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #ccc; margin-bottom: 15px;"></i>
                <p>Đang tải dữ liệu báo cáo...</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-column">
                <h3>Thư viện Sách</h3>
                <p>Hệ thống quản lý thư viện hiện đại với đầy đủ tính năng quản trị, giúp quản lý sách và độc giả một cách hiệu quả.</p>
                <div class="social-links" style="margin-top: 20px;">
                    <a href="#" style="color: #aaa; margin-right: 15px; font-size: 1.2rem;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: #aaa; margin-right: 15px; font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #aaa; font-size: 1.2rem;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Liên kết nhanh</h3>
                <ul class="footer-links">
                    <li><a href="#home">Trang chủ</a></li>
                    <li><a href="#books">Sách gần đây</a></li>
                    <li><a href="#reports">Báo cáo</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Liên hệ</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-envelope" style="margin-right: 10px;"></i> vuduytien@gmail.com</li>
                    <li><i class="fas fa-phone" style="margin-right: 10px;"></i> (024) 68 869 999</li>
                    <li><i class="fas fa-map-marker-alt" style="margin-right: 10px;"></i> Hà Nội, Việt Nam</li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            &copy; 2025 Hệ thống Quản trị Thư viện Sách. Phiên bản dành cho quản trị viên.
        </div>
    </footer>

    <script>
    function formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }
    
    function formatCurrency(amount) {
        if (!amount || amount == 0) return '0 VNĐ';
        return formatNumber(amount) + ' VNĐ';
    }
    
    function getStatusBadge(status) {
        switch(status) {
            case 'Còn':
                return '<span class="book-status status-available">Có sẵn</span>';
            case 'Hết':
                return '<span class="book-status status-unavailable">Đã hết</span>';
            case 'Ngưng sử dụng':
                return '<span class="book-status" style="background: #d1d5db; color: #374151; border: 1px solid #9ca3af;">Ngưng</span>';
            default:
                return '<span class="book-status" style="background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db;">' + (status || 'N/A') + '</span>';
        }
    }
    
    async function loadStats() {
        try {
            const response = await fetch('get_dashboard_stats.php');
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            console.log('Stats data:', data);
            
            if (data.success) {
                document.getElementById('total-admins').textContent = formatNumber(data.total_admins);
                document.getElementById('total-books').textContent = formatNumber(data.total_books);
                document.getElementById('total-readers').textContent = formatNumber(data.total_readers);
                document.getElementById('total-borrows').textContent = formatNumber(data.total_borrows);
            } else {
                console.error('Stats API error:', data.message);
            }
        } catch (error) {
            console.error('Lỗi tải thống kê:', error);
            // Hiển thị giá trị mặc định
            document.getElementById('total-books').textContent = '0';
        }
    }
    
    async function loadRecentBooks() {
        try {
            console.log('Đang tải sách gần đây...');
            const response = await fetch('get_recent_books.php');
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            console.log('Books data:', data);
            
            const container = document.getElementById('recent-books');
            
            if (data.success && data.books && data.books.length > 0) {
                let html = `
                <div class="books-management">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã sách</th>
                                    <th>Thông tin sách</th>
                                    <th>Nội dung</th>
                                    <th>Thể loại</th>
                                    <th>Nhà xuất bản</th>
                                    <th>Số lượng</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                data.books.forEach(book => {
                    // Rút gọn nội dung nếu quá dài
                    const content = book.Noi_dung || '';
                    const shortContent = content.length > 50 ? content.substring(0, 50) + '...' : content;
                    
                    html += `
                                <tr>
                                    <td>
                                        <strong style="color: #0d6efd;">${book.Ma_sach || 'N/A'}</strong>
                                    </td>
                                    <td>
                                        <div class="book-title">${book.Ten_sach || 'N/A'}</div>
                                        <div class="book-author">${book.Ten_tac_gia || 'Không rõ'}</div>
                                    </td>
                                    <td>
                                        <div class="book-content" title="${book.Noi_dung || ''}">
                                            ${shortContent}
                                        </div>
                                    </td>
                                    <td>
                                        ${book.Ten_the_loai ? 
                                            `<div class="categories-list">${book.Ten_the_loai}</div>` : 
                                            '<span style="color: #666; font-size: 0.85rem;">Chưa phân loại</span>'
                                        }
                                    </td>
                                    <td>${book.Nha_xuat_ban || 'N/A'}</td>
                                    <td>
                                        <strong style="color: #0d6efd;">${book.So_ban || 0}</strong>
                                        ${book.So_ban <= 2 ? '<br><small style="color: #dc3545;">⚠️ Sắp hết</small>' : ''}
                                    </td>
                                    <td>
                                        ${getStatusBadge(book.Trang_thai)}
                                    </td>
                                </tr>`;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                </div>`;
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                <div style="text-align: center; padding: 40px 20px; color: #666; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="font-size: 48px; margin-bottom: 16px;">📚</div>
                    <h4 style="margin-bottom: 8px; color: #495057;">Chưa có sách nào</h4>
                    <p style="margin-bottom: 20px;">Thư viện hiện chưa có sách trong hệ thống.</p>
                    <a href="add_book.php" style="display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px; font-weight: 500;">
                        Thêm sách đầu tiên
                    </a>
                </div>`;
            }
        } catch (error) {
            console.error('Lỗi tải sách:', error);
            container.innerHTML = `
            <div style="text-align: center; padding: 20px; color: #dc3545; background: #f8d7da; border-radius: 8px; border: 1px solid #f5c2c7;">
                ❌ Lỗi tải dữ liệu sách: ${error.message}
                <br><small>Vui lòng kiểm tra console (F12) để biết thêm chi tiết</small>
            </div>`;
        }
    }
    
    async function loadRecentReports() {
        try {
            const response = await fetch('get_recent_reports.php');
            const data = await response.json();
            const container = document.getElementById('recent-reports');
            
            if (data.success && data.reports && data.reports.length > 0) {
                let html = `<table class="reports-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Loại báo cáo</th>
                            <th>Thời gian</th>
                            <th>Người tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>`;
                
                data.reports.forEach(report => {
                    const date = report.Thoi_gian_tao ? new Date(report.Thoi_gian_tao) : new Date();
                    html += `<tr>
                        <td><strong>${report.Ma_bao_cao || 'N/A'}</strong></td>
                        <td>${report.Loai_bao_cao || 'N/A'}</td>
                        <td>${date.toLocaleString('vi-VN')}</td>
                        <td>${report.Nguoi_tao || 'N/A'}</td>
                        <td>
                            <a href="view_report.php?id=${report.Ma_bao_cao}">
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>`;
                });
                
                html += `</tbody></table>`;
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p style="text-align: center; padding: 20px; color: #6c757d;">Chưa có báo cáo nào</p>';
            }
        } catch (error) {
            console.error('Lỗi tải báo cáo:', error);
            container.innerHTML = '<p style="color: #dc3545; text-align: center;">Lỗi tải dữ liệu báo cáo</p>';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Dashboard đang tải dữ liệu...');
        loadStats();
        loadRecentBooks();
        loadRecentReports();
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>