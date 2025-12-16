<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'docgia') {
    header('Location: ../auth/login.php');
    exit();
}

$user = $_SESSION['user'];
$username = $user['username'];

// Lấy thông tin độc giả
$stmt = $pdo->prepare("SELECT * FROM DOC_GIA WHERE Ma_doc_gia = ?");
$stmt->execute([$username]);
$doc_gia = $stmt->fetch();

// Lấy lịch sử mượn (tất cả phiếu)
$stmt = $pdo->prepare("SELECT p.*, 
                       COUNT(c.Ma_sach) as so_sach,
                       p.Trang_thai,
                       DATEDIFF(p.Ngay_hen_tra, CURDATE()) as con_lai
                       FROM PHIEU_MUON p
                       LEFT JOIN CHI_TIET_MUON c ON p.Ma_phieu_muon = c.Ma_phieu_muon
                       WHERE p.Ma_doc_gia = ?
                       GROUP BY p.Ma_phieu_muon
                       ORDER BY p.Ngay_muon DESC");
$stmt->execute([$username]);
$borrow_history = $stmt->fetchAll();

// Đếm số sách trong giỏ
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM gio_muon_tam WHERE Ma_doc_gia = ?");
$stmt->execute([$username]);
$cart_count = $stmt->fetch()['count'];

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
    <title>Lịch sử mượn sách</title>
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
        
        /* Main container */
        .container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 20px;
        }
        
        .page-title {
            color: white;
            margin-bottom: 40px;
            font-size: 2.5rem;
            text-align: center;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .page-title i {
            color: #26d0ce;
            margin-right: 15px;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-section .total-count {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a2980;
        }
        
        .filter-section .total-count .count {
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            margin-left: 10px;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 12px 20px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #475569;
            font-weight: 500;
            font-size: 1rem;
            min-width: 200px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #26d0ce;
            box-shadow: 0 0 0 3px rgba(38, 208, 206, 0.2);
        }
        
        /* History Cards */
        .history-cards-container {
            display: grid;
            gap: 25px;
        }
        
        .history-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 5px solid #26d0ce;
            position: relative;
        }
        
        .history-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .history-card.completed {
            border-left-color: #28a745;
        }
        
        .history-card.overdue {
            border-left-color: #ff6b6b;
            background: linear-gradient(135deg, #fff5f5, #ffffff);
        }
        
        .history-card.cancelled {
            border-left-color: #6c757d;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
        }
        
        .history-card.active {
            border-left-color: #26d0ce;
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .history-id {
            font-weight: bold;
            color: #1a2980;
            font-size: 1.4rem;
        }
        
        .history-status {
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-active {
            background: rgba(38, 208, 206, 0.15);
            color: #26d0ce;
        }
        
        .status-completed {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }
        
        .status-overdue {
            background: rgba(255, 107, 107, 0.15);
            color: #ff6b6b;
        }
        
        .status-cancelled {
            background: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
        
        .history-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            color: #1a2980;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        /* Books List */
        .books-list {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .books-list-title {
            color: #1a2980;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .book-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .book-item:hover {
            background: white;
        }
        
        .book-item:last-child {
            border-bottom: none;
        }
        
        .book-info {
            flex: 1;
        }
        
        .book-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .book-author {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        /* Action Buttons */
        .history-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(26, 41, 128, 0.3);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #475569;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-3px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 25px;
            display: block;
        }
        
        .empty-state h3 {
            color: #475569;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        
        .empty-state p {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Footer */
        footer {
            background-color: #0c1127;
            color: #aaa;
            padding: 60px 20px 30px;
            margin-top: 80px;
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
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 50px;
        }
        
        .pagination-btn {
            padding: 12px 20px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-numbers {
            display: flex;
            gap: 5px;
        }
        
        .pagination-number {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .pagination-number:hover {
            background: #f8f9fa;
        }
        
        .pagination-number.active {
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            color: white;
            border-color: #1a2980;
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
            
            .container {
                margin: 140px auto 30px;
                padding: 15px;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .filter-section {
                flex-direction: column;
                align-items: stretch;
                padding: 20px;
            }
            
            .history-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .history-details {
                grid-template-columns: 1fr;
            }
            
            .history-card {
                padding: 20px;
            }
            
            .history-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .book-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .nav-links {
                gap: 10px;
            }
            
            .nav-links a {
                font-size: 0.9rem;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
            
            .history-id {
                font-size: 1.2rem;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
            
            .pagination-btn span {
                display: none;
            }
            
            .pagination-btn i {
                margin: 0;
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
                    <i class="fas fa-shopping-cart"></i> Giỏ mượn
                    <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="my_borrows.php">
                    <i class="fas fa-book"></i> Sách đang mượn
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
    
    <!-- Main Content -->
    <div class="container">
        <h1 class="page-title">
         Lịch sử mượn sách
        </h1>
        
        <?php if (empty($borrow_history)): ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h3>Chưa có lịch sử mượn sách</h3>
                <p>Bạn chưa từng mượn sách nào. Hãy khám phá thư viện và bắt đầu mượn sách ngay!</p>
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-book-open"></i> Mượn sách ngay
                </a>
            </div>
        <?php else: ?>
            
            <!-- History Cards -->
            <div class="history-cards-container">
                <?php foreach ($borrow_history as $borrow): 
                    // Lấy chi tiết sách trong phiếu mượn
                    $stmt = $pdo->prepare("SELECT c.*, s.Ten_sach, s.Ten_tac_gia 
                                          FROM CHI_TIET_MUON c 
                                          JOIN SACH s ON c.Ma_sach = s.Ma_sach 
                                          WHERE c.Ma_phieu_muon = ?");
                    $stmt->execute([$borrow['Ma_phieu_muon']]);
                    $books = $stmt->fetchAll();
                    
                    // Xác định trạng thái và class
                    $status = $borrow['Trang_thai'];
                    $card_class = '';
                    $status_class = '';
                    $status_text = $status;
                    $status_icon = '';
                    
                    // Kiểm tra nếu quá hạn (tự động tính toán)
                    if ($status === 'Đang mượn' && $borrow['con_lai'] < 0) {
                        $status = 'Quá hạn';
                        $status_text = 'Quá hạn ' . abs($borrow['con_lai']) . ' ngày';
                    }
                    
                    switch($status) {
                        case 'Đang mượn':
                            $card_class = 'active';
                            $status_class = 'status-active';
                            $status_icon = 'fas fa-clock';
                            if ($borrow['con_lai'] <= 2 && $borrow['con_lai'] >= 0) {
                                $status_text = 'Sắp hết hạn (' . $borrow['con_lai'] . ' ngày)';
                            } else {
                                $status_text = 'Đang mượn (' . $borrow['con_lai'] . ' ngày còn lại)';
                            }
                            break;
                        case 'Đã trả':
                            $card_class = 'completed';
                            $status_class = 'status-completed';
                            $status_icon = 'fas fa-check-circle';
                            break;
                        case 'Quá hạn':
                            $card_class = 'overdue';
                            $status_class = 'status-overdue';
                            $status_icon = 'fas fa-exclamation-triangle';
                            break;
                        case 'Đã hủy':
                            $card_class = 'cancelled';
                            $status_class = 'status-cancelled';
                            $status_icon = 'fas fa-ban';
                            break;
                    }
                ?>
                <div class="history-card <?php echo $card_class; ?>" data-status="<?php echo $borrow['Trang_thai']; ?>" data-days="<?php echo $borrow['con_lai']; ?>">
                    <div class="history-header">
                        <div>
                            <div class="history-id">Mã phiếu: <?php echo htmlspecialchars($borrow['Ma_phieu_muon']); ?></div>
                            <div style="color: #64748b; font-size: 0.9rem; margin-top: 5px;">
                                Tạo ngày: <?php echo date('d/m/Y', strtotime($borrow['Ngay_muon'])); ?>
                            </div>
                        </div>
                        <span class="history-status <?php echo $status_class; ?>">
                            <i class="<?php echo $status_icon; ?>"></i>
                            <?php echo $status_text; ?>
                        </span>
                    </div>
                    
                    <div class="history-details">
                        <div class="detail-item">
                            <div class="detail-label">Ngày mượn</div>
                            <div class="detail-value"><?php echo date('d/m/Y', strtotime($borrow['Ngay_muon'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Hạn trả</div>
                            <div class="detail-value"><?php echo date('d/m/Y', strtotime($borrow['Ngay_hen_tra'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Số sách</div>
                            <div class="detail-value"><?php echo $borrow['so_sach']; ?> cuốn</div>
                        </div>

                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($books)): ?>
                    <div class="books-list">
                        <div class="books-list-title">
                            <i class="fas fa-list"></i>
                            Danh sách sách (<?php echo count($books); ?> cuốn)
                        </div>
                        <?php foreach ($books as $book): ?>
                        <div class="book-item">
                            <div class="book-info">
                                <div class="book-title"><?php echo htmlspecialchars($book['Ten_sach']); ?></div>
                                <div class="book-author"><?php echo htmlspecialchars($book['Ten_tac_gia'] ?? 'Không rõ'); ?></div>
                            </div>
                            <div style="color: #64748b; font-size: 0.9rem; text-align: right;">
                                Mã: <?php echo htmlspecialchars($book['Ma_sach']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
    
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
                    <li><a href="borrow_history.php">Lịch sử mượn</a></li>
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
        function showDetails(borrowId) {
            window.location.href = 'borrow_detail.php?id=' + borrowId;
        }
        
        function showRenewModal(borrowId) {
            alert('Tính năng yêu cầu gia hạn cho phiếu ' + borrowId + ' đang được phát triển!');
        }
        
        // Lọc theo trạng thái
        document.getElementById('statusFilter').addEventListener('change', function() {
            filterAndSortCards();
        });
        
        // Sắp xếp
        document.getElementById('sortFilter').addEventListener('change', function() {
            filterAndSortCards();
        });
        
        function filterAndSortCards() {
            const statusFilter = document.getElementById('statusFilter').value;
            const sortFilter = document.getElementById('sortFilter').value;
            const cards = Array.from(document.querySelectorAll('.history-card'));
            
            // Lọc theo trạng thái
            cards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const days = parseInt(card.getAttribute('data-days'));
                
                let showCard = false;
                
                if (statusFilter === 'all') {
                    showCard = true;
                } else if (statusFilter === 'Đang mượn') {
                    showCard = cardStatus === 'Đang mượn';
                } else if (statusFilter === 'Đã trả') {
                    showCard = cardStatus === 'Đã trả';
                } else if (statusFilter === 'Quá hạn') {
                    showCard = cardStatus === 'Đang mượn' && days < 0;
                } else if (statusFilter === 'Đã hủy') {
                    showCard = cardStatus === 'Đã hủy';
                }
                
                card.style.display = showCard ? '' : 'none';
            });
            
            // Sắp xếp
            const visibleCards = cards.filter(card => card.style.display !== 'none');
            
            visibleCards.sort((a, b) => {
                const aDays = parseInt(a.getAttribute('data-days'));
                const bDays = parseInt(b.getAttribute('data-days'));
                
                switch(sortFilter) {
                    case 'newest':
                        return 0; // Đã được sắp xếp từ MySQL
                    case 'oldest':
                        return 0; // Đã được sắp xếp từ MySQL
                    case 'due_soon':
                        return aDays - bDays; // Sắp đến hạn trước
                    case 'overdue':
                        if (aDays < 0 && bDays >= 0) return -1;
                        if (aDays >= 0 && bDays < 0) return 1;
                        return aDays - bDays; // Quá hạn nhiều nhất trước
                    default:
                        return 0;
                }
            });
            
            // Sắp xếp lại DOM
            const container = document.querySelector('.history-cards-container');
            visibleCards.forEach(card => {
                container.appendChild(card);
            });
        }
        
        // Hiệu ứng hover cho thẻ lịch sử
        document.querySelectorAll('.history-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
            });
        });
        
        // Hiệu ứng hover cho thẻ sách
        document.querySelectorAll('.book-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.background = 'white';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.background = '';
            });
        });
        
        // Tự động chọn bộ lọc từ URL nếu có
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            if (status) {
                const filter = document.getElementById('statusFilter');
                filter.value = status;
                filterAndSortCards();
            }
        });
    </script>
</body>
</html>