<?php
session_start();
require '../config/db.php';

// Kiểm tra đăng nhập và role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'docgia') {
    header('Location: ../auth/login.php');
    exit();
}

$user = $_SESSION['user'];
$username = $user['username'];

// Lấy thông tin độc giả từ bảng DOC_GIA
$stmt = $pdo->prepare("SELECT * FROM DOC_GIA WHERE Ma_doc_gia = ?");
$stmt->execute([$username]);
$doc_gia = $stmt->fetch();

// Nếu chưa có thông tin, tạo mới
if (!$doc_gia) {
    $stmt = $pdo->prepare("INSERT INTO DOC_GIA (Ma_doc_gia, Ho_ten, Email) VALUES (?, ?, ?)");
    $stmt->execute([$username, $username, $user['email'] ?? '']);
    
    $stmt = $pdo->prepare("SELECT * FROM DOC_GIA WHERE Ma_doc_gia = ?");
    $stmt->execute([$username]);
    $doc_gia = $stmt->fetch();
}

// Xử lý tìm kiếm sách
$search_results = [];
$search_query = '';
$has_search = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $has_search = true;
    
    // Tìm kiếm sách theo tên, tác giả, thể loại
    $search_term = '%' . $search_query . '%';
    $stmt = $pdo->prepare("
        SELECT * FROM SACH 
        WHERE (Ten_sach LIKE ? 
               OR Ten_tac_gia LIKE ? 
               OR Ten_the_loai LIKE ?
               OR Ma_sach LIKE ?)
        ORDER BY Ten_sach
        LIMIT 20
    ");
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
    $search_results = $stmt->fetchAll();
}

// Thống kê sách đang mượn
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM PHIEU_MUON WHERE Ma_doc_gia = ? AND Trang_thai = 'Đang mượn'");
$stmt->execute([$username]);
$borrowing_count = $stmt->fetch()['count'];

// Thống kê sách sắp hết hạn
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT p.Ma_phieu_muon) as count 
    FROM PHIEU_MUON p
    JOIN CHI_TIET_MUON c ON p.Ma_phieu_muon = c.Ma_phieu_muon
    WHERE p.Ma_doc_gia = ? 
    AND p.Trang_thai = 'Đang mượn'
    AND DATEDIFF(p.Ngay_hen_tra, CURDATE()) <= 2
    AND DATEDIFF(p.Ngay_hen_tra, CURDATE()) >= 0
");
$stmt->execute([$username]);
$due_soon_count = $stmt->fetch()['count'];

// Thống kê sách quá hạn
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT p.Ma_phieu_muon) as count 
    FROM PHIEU_MUON p
    JOIN CHI_TIET_MUON c ON p.Ma_phieu_muon = c.Ma_phieu_muon
    WHERE p.Ma_doc_gia = ? 
    AND p.Trang_thai = 'Đang mượn'
    AND DATEDIFF(p.Ngay_hen_tra, CURDATE()) < 0
");
$stmt->execute([$username]);
$overdue_count = $stmt->fetch()['count'];

// Thống kê sách trong giỏ
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM gio_muon_tam WHERE Ma_doc_gia = ?");
$stmt->execute([$username]);
$cart_count = $stmt->fetch()['count'];

// Lấy sách đề xuất (sách còn sẵn)
$recommended_books = [];
try {
    $stmt = $pdo->query("SELECT * FROM SACH WHERE Trang_thai = 'Còn' AND (So_ban - So_ban_dang_muon) > 0 ORDER BY RAND() LIMIT 6");
    $recommended_books = $stmt->fetchAll();
} catch (Exception $e) {
    $recommended_books = [];
}

// Hàm lấy chữ cái đầu cho avatar
function getAvatarInitials($name) {
    if (empty($name)) return 'DG';
    
    $names = explode(' ', $name);
    $initials = '';
    
    if (count($names) >= 2) {
        $initials = strtoupper(substr($names[0], 0, 1) . substr($names[count($names)-1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }
    
    return $initials;
}

$userFullName = $doc_gia['Ho_ten'] ?? $username;
$avatarInitials = getAvatarInitials($userFullName);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện Sách - Bảng điều khiển Độc giả</title>
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
            display: flex;
            align-items: center;
            gap: 8px;
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
        
        .cart-badge {
            background: #ff6b6b;
            color: white;
            font-size: 0.8rem;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
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
        
        .search-box {
            max-width: 600px;
            margin: 40px auto;
            position: relative;
        }
        
        .search-box form {
            display: flex;
            gap: 0;
        }
        
        .search-box input {
            flex: 1;
            padding: 18px 25px;
            border-radius: 50px 0 0 50px;
            border: 2px solid #ddd;
            font-size: 1.1rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-right: none;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #26d0ce;
            box-shadow: 0 5px 25px rgba(38, 208, 206, 0.2);
        }
        
        .search-box button {
            background-color: #1a2980;
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 0 50px 50px 0;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #1a2980;
        }
        
        .search-box button:hover {
            background-color: #26d0ce;
            border-color: #26d0ce;
        }
        
        /* Search Results Section */
        .search-results-section {
            padding: 80px 20px;
            background-color: white;
        }
        
        .search-results-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .search-results-title {
            color: #1a2980;
            margin-bottom: 20px;
            font-size: 2.2rem;
        }
        
        .search-results-count {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .no-results i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            color: #666;
            margin-bottom: 15px;
        }
        
        .no-results p {
            color: #888;
            font-size: 1.1rem;
            margin-bottom: 20px;
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
        
        .stat-card.warning {
            border-left-color: #ffc107;
        }
        
        .stat-card.cart {
            border-left-color: #6f42c1;
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
        
        .stat-card.warning .stat-number {
            color: #ffc107;
        }
        
        .stat-card.cart .stat-number {
            color: #6f42c1;
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(26, 41, 128, 0.3);
            border-color: white;
        }
        
        /* Books Section */
        .books-section {
            padding: 80px 20px;
            background-color: #f8f9fa;
        }
        
        .books-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }
        
        .book-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }
        
        .book-info {
            padding: 25px 20px;
        }
        
        .book-title {
            font-weight: 700;
            margin-bottom: 8px;
            color: #1a2980;
            font-size: 1.2rem;
            line-height: 1.4;
            height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
        }
        
        .book-author {
            color: #666;
            font-size: 1rem;
            margin-bottom: 15px;
            height: 20px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        
        .book-status {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-available {
            background: rgba(38, 208, 206, 0.1);
            color: #26d0ce;
        }
        
        .status-unavailable {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
        }
        
        /* Alert Section */
        .alert-section {
            padding: 80px 20px;
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            text-align: center;
        }
        
        .alert-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .alert-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .alert-section p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.8;
        }
        
        /* Call to Action */
        .cta-section {
            padding: 100px 20px;
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            text-align: center;
            color: white;
        }
        
        .cta-content {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .cta-content h2 {
            font-size: 2.8rem;
            margin-bottom: 20px;
        }
        
        .cta-content p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-btn {
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .cta-btn.primary {
            background-color: white;
            color: #1a2980;
        }
        
        .cta-btn.primary:hover {
            background-color: #f0f0f0;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .cta-btn.secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .cta-btn.secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
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
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .search-results-title {
                font-size: 1.8rem;
            }
            
            .cta-content h2 {
                font-size: 2.2rem;
            }
            
            .user-dropdown {
                position: fixed;
                top: 80px;
                right: 20px;
                left: 20px;
                width: auto;
            }
            
            .stats-container,
            .actions-container,
            .books-container {
                grid-template-columns: 1fr;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .cta-btn {
                width: 100%;
                max-width: 300px;
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
            
            .search-box input {
                padding: 15px 20px;
                font-size: 1rem;
            }
            
            .search-box button {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span>Thư viện Sách</span>
            </a>
            
            <div class="nav-links">
                <a href="index.php#home">Trang chủ</a>
                <a href="index.php#books">Sách</a>
                <a href="cart.php">
                    Giỏ mượn
                    <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="my_borrows.php">
                  Sách đang mượn
                </a>
                <a href="edit_user.php">Hồ sơ</a>
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
                            <h4><?php echo htmlspecialchars($userFullName); ?></h4>
                            <p>Độc giả</p>
                        </div>
                    </div>
                    
                    <ul class="dropdown-menu">
                        <li><a href="my_borrows.php"> Sách đang mượn</a></li>
                        <li><a href="cart.php"> Giỏ mượn</a></li>
                        <li><a href="borrow_history.php"> Lịch sử mượn</a></li>
                        <div class="dropdown-divider"></div>
                        <li><a href="edit_user.php"> Thông tin cá nhân</a></li>
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
            <h1>Chào mừng <?php echo htmlspecialchars($doc_gia['Ho_ten'] ?? $username); ?> đến Thư viện Sách!</h1>
            <p>Khám phá thế giới tri thức với hàng nghìn đầu sách đa dạng</p>
            
            <div class="search-box">
                <form method="GET" action="">
                    <input type="text" name="search" 
                           value="<?php echo htmlspecialchars($search_query); ?>" 
                           placeholder="Tìm kiếm sách, tác giả hoặc thể loại..."
                           autocomplete="off">
                    <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Search Results Section -->
    <?php if ($has_search): ?>
    <section class="search-results-section">
        <div class="search-results-container">
            <h2 class="search-results-title">
                <i class="fas fa-search"></i> Kết quả tìm kiếm cho "<?php echo htmlspecialchars($search_query); ?>"
            </h2>
            
            <p class="search-results-count">
                Tìm thấy <strong><?php echo count($search_results); ?></strong> kết quả
            </p>
            
            <?php if (!empty($search_results)): ?>
            <div class="books-container">
                <?php foreach ($search_results as $book): 
                    $so_ban_con = $book['So_ban'] - $book['So_ban_dang_muon'];
                    $co_the_muon = ($book['Trang_thai'] === 'Còn') && ($so_ban_con > 0);
                ?>
                <a href="view_book.php?id=<?php echo urlencode($book['Ma_sach']); ?>" class="book-card">
                    <div class="book-info">
                        <div class="book-title">
                            <?php echo htmlspecialchars($book['Ten_sach']); ?>
                        </div>
                        <div class="book-author">
                            <?php echo htmlspecialchars($book['Ten_tac_gia'] ?? 'Không rõ'); ?>
                        </div>
                        <?php 
                        $status_class = 'status-available';
                        $status_text = 'Có sẵn';
                        
                        if (!$co_the_muon) {
                            $status_class = 'status-unavailable';
                            $status_text = 'Đã hết';
                        }
                        ?>
                        <span class="book-status <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Không tìm thấy kết quả phù hợp</h3>
                <p>Hãy thử với từ khóa khác hoặc tìm kiếm ít cụ thể hơn</p>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    <!-- Alert Section (nếu có sách quá hạn) -->
    <?php if ($overdue_count > 0): ?>
    <section class="alert-section">
        <div class="alert-content">
            <h2><i class="fas fa-exclamation-triangle"></i> Cảnh báo!</h2>
            <p>Bạn có <strong><?php echo $overdue_count; ?> phiếu mượn</strong> đã quá hạn trả. Vui lòng liên hệ thư viện để trả sách và tránh bị phạt.</p>
            <a href="my_borrows.php" class="cta-btn secondary" style="background: white; color: #ff6b6b;">
                <i class="fas fa-book"></i> Kiểm tra ngay
            </a>
        </div>
    </section>
    <?php elseif ($due_soon_count > 0): ?>
    <section class="alert-section" style="background: linear-gradient(135deg, #ffc107, #ffd54f);">
        <div class="alert-content">
            <h2><i class="fas fa-clock"></i> Lưu ý!</h2>
            <p>Bạn có <strong><?php echo $due_soon_count; ?> phiếu mượn</strong> sắp hết hạn. Vui lòng kiểm tra và trả sách đúng hạn.</p>
            <a href="my_borrows.php" class="cta-btn secondary" style="background: white; color: #ffc107;">
                <i class="fas fa-book"></i> Kiểm tra ngay
            </a>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Books Section -->
    <section class="books-section" id="books">
        <div class="section-title">
            <h2>Sách đang có trong hệ thống thư viện</h2>
            <p>Khám phá những cuốn sách mới và thú vị</p>
        </div>
    
        <div class="books-container">
            <?php if (!empty($recommended_books)): ?>
                <?php foreach ($recommended_books as $book): 
                    $so_ban_con = $book['So_ban'] - $book['So_ban_dang_muon'];
                    $co_the_muon = ($book['Trang_thai'] === 'Còn') && ($so_ban_con > 0);
                ?>
                <a href="view_book.php?id=<?php echo urlencode($book['Ma_sach']); ?>" class="book-card">
                    <div class="book-info">
                        <div class="book-title">
                            <?php echo htmlspecialchars($book['Ten_sach']); ?>
                        </div>
                        <div class="book-author">
                            <?php echo htmlspecialchars($book['Ten_tac_gia'] ?? 'Không rõ'); ?>
                        </div>
                        <?php 
                        $status_class = 'status-available';
                        $status_text = 'Có sẵn';
                        
                        if (!$co_the_muon) {
                            $status_class = 'status-unavailable';
                            $status_text = 'Đã hết';
                        }
                        ?>
                        <span class="book-status <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-book" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                    <p>Hiện không có sách để hiển thị.</p>
                </div>
            <?php endif; ?>
        </div>

    </section>
    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-column">
                <h3>Thư viện Sách</h3>
                <p>Nền tảng đọc sách trực tuyến hàng đầu với kho tàng tri thức phong phú, đa dạng thể loại. Mang tri thức đến với mọi người.</p>
                <div class="social-links" style="margin-top: 20px;">
                    <a href="#" style="color: #aaa; margin-right: 15px; font-size: 1.2rem;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: #aaa; margin-right: 15px; font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #aaa; font-size: 1.2rem;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Liên kết nhanh</h3>
                <ul class="footer-links">
                    <li><a href="index.php#home">Trang chủ</a></li>
                    <li><a href="index.php#books">Sách</a></li>
                    <li><a href="cart.php">Giỏ mượn</a></li>
                    <li><a href="my_borrows.php">Sách đang mượn</a></li>
                    <li><a href="edit_user.php">Hồ sơ</a></li>
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
            &copy; 2025 Thư viện Sách Trực tuyến. Phiên bản dành cho độc giả.
        </div>
    </footer>

    <script>
        // Xử lý form tìm kiếm
        document.querySelector('.search-box form')?.addEventListener('submit', function(e) {
            const searchTerm = document.querySelector('.search-box input').value.trim();
            if (!searchTerm) {
                e.preventDefault();
                alert('Vui lòng nhập từ khóa tìm kiếm!');
                return;
            }
        });

        // Allow Enter key to trigger search
        document.querySelector('.search-box input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (!searchTerm) {
                    e.preventDefault();
                    alert('Vui lòng nhập từ khóa tìm kiếm!');
                }
            }
        });
        
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
        
        // Auto-hide message after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s';
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
        
        // Thêm hiệu ứng hover cho thẻ sách
        document.querySelectorAll('.book-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.08)';
            });
        });
        
        // Tự động focus vào ô tìm kiếm
        document.querySelector('.search-box input')?.focus();
    </script>
</body>
</html>