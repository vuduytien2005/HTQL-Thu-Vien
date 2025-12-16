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

// Lấy sách trong giỏ
$stmt = $pdo->prepare("SELECT g.*, s.So_ban, s.So_ban_dang_muon 
                       FROM gio_muon_tam g 
                       JOIN SACH s ON g.Ma_sach = s.Ma_sach 
                       WHERE g.Ma_doc_gia = ?");
$stmt->execute([$username]);
$cart_items = $stmt->fetchAll();

// Xử lý xóa sách khỏi giỏ
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM gio_muon_tam WHERE id = ? AND Ma_doc_gia = ?");
    $stmt->execute([$remove_id, $username]);
    $_SESSION['success'] = "Đã xóa sách khỏi giỏ!";
    header('Location: cart.php');
    exit();
}

// Xử lý tạo phiếu mượn
if (isset($_POST['create_borrow']) && count($cart_items) > 0) {
    try {
        $pdo->beginTransaction();
        
        // Tạo mã phiếu mượn mới
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM PHIEU_MUON");
        $result = $stmt->fetch();
        $new_id = 'PM' . str_pad($result['count'] + 1, 6, '0', STR_PAD_LEFT);
        
        // Ngày mượn: hôm nay, Ngày hẹn trả: +7 ngày
        $ngay_muon = date('Y-m-d');
        $ngay_hen_tra = date('Y-m-d', strtotime('+7 days'));
        
        // Tạo phiếu mượn
        $stmt = $pdo->prepare("INSERT INTO PHIEU_MUON (Ma_phieu_muon, Ma_doc_gia, Ngay_muon, Ngay_hen_tra, Tong_so_sach, Trang_thai) 
                               VALUES (?, ?, ?, ?, ?, 'Đang mượn')");
        $stmt->execute([$new_id, $username, $ngay_muon, $ngay_hen_tra, count($cart_items)]);
        
        // Thêm chi tiết mượn và cập nhật số sách đang mượn
        foreach ($cart_items as $item) {
            // Thêm chi tiết mượn
            $stmt = $pdo->prepare("INSERT INTO CHI_TIET_MUON (Ma_phieu_muon, Ma_sach) VALUES (?, ?)");
            $stmt->execute([$new_id, $item['Ma_sach']]);
            
            // Cập nhật số sách đang mượn
            $stmt = $pdo->prepare("UPDATE SACH SET So_ban_dang_muon = So_ban_dang_muon + 1 WHERE Ma_sach = ?");
            $stmt->execute([$item['Ma_sach']]);
        }
        
        // Xóa giỏ sau khi tạo phiếu thành công
        $stmt = $pdo->prepare("DELETE FROM gio_muon_tam WHERE Ma_doc_gia = ?");
        $stmt->execute([$username]);
        
        $pdo->commit();
        
        $_SESSION['success'] = "Tạo phiếu mượn thành công! Mã phiếu: " . $new_id;
        header('Location: my_borrows.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
    }
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

// Đếm số sách trong giỏ
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM gio_muon_tam WHERE Ma_doc_gia = ?");
$stmt->execute([$username]);
$cart_count = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ sách muốn mượn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            max-width: 1000px;
            margin: 120px auto 50px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .page-title {
            color: #1a2980;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #26d0ce;
            font-size: 2rem;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .cart-table th {
            background: #f8f9fa;
            padding: 20px;
            text-align: left;
            color: #1a2980;
            border-bottom: 3px solid #26d0ce;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .cart-table td {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .cart-table tr:hover {
            background: #f8fafc;
        }
        
        .cart-table tr:last-child td {
            border-bottom: none;
        }
        
        .remove-btn {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .remove-btn:hover {
            background: #fef2f2;
            transform: scale(1.1);
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 0;
            border-top: 2px solid #e2e8f0;
            margin-top: 30px;
        }
        
        .btn {
            padding: 14px 35px;
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
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #cbd5e1;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }
        
        .empty-cart i {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 25px;
            display: block;
        }
        
        .empty-cart h3 {
            color: #475569;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .empty-cart p {
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .book-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-available {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-limited {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-unavailable {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .info-box {
            background: #f8fafc;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
            border-left: 4px solid #3b82f6;
        }
        
        .info-box h4 {
            color: #1e293b;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .info-box ul {
            margin-left: 20px;
            color: #475569;
        }
        
        .info-box li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
                font-size: 1.7rem;
            }
            
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            
            .cart-table th,
            .cart-table td {
                padding: 12px 15px;
            }
            
            .cart-actions {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
                font-size: 1.5rem;
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
            <i class="fas fa-shopping-cart"></i> Giỏ sách muốn mượn
        </h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-cart-arrow-down"></i>
                <h3>Giỏ mượn trống</h3>
                <p>Bạn chưa có sách nào trong giỏ mượn</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên sách</th>
                            <th>Trạng thái</th>
                            <th>Thời gian thêm</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $index => $item): 
                            $so_ban_con = $item['So_ban'] - $item['So_ban_dang_muon'];
                            
                            if ($so_ban_con > 2) {
                                $status_class = 'status-available';
                                $status_text = 'Có sẵn';
                            } elseif ($so_ban_con > 0) {
                                $status_class = 'status-limited';
                                $status_text = 'Số lượng ít';
                            } else {
                                $status_class = 'status-unavailable';
                                $status_text = 'Đã hết';
                            }
                        ?>
                        <tr>
                            <td><strong><?php echo $index + 1; ?></strong></td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($item['Ten_sach']); ?></div>
                                <div style="font-size: 0.9rem; color: #64748b; margin-top: 5px;">
                                    Mã sách: <?php echo htmlspecialchars($item['Ma_sach']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="book-status <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                                <?php if ($so_ban_con > 0): ?>
                                <div style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">
                                    Còn <?php echo $so_ban_con; ?> bản
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($item['Thoi_gian_them'])); ?>
                                <div style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">
                                    <?php echo date('H:i', strtotime($item['Thoi_gian_them'])); ?>
                                </div>
                            </td>
                            <td>
                                <button onclick="if(confirm('Xóa sách này khỏi giỏ?')) window.location.href='?remove=<?php echo $item['id']; ?>'" 
                                        class="remove-btn" title="Xóa sách">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="cart-actions">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Tiếp tục tìm sách
                </a>
                
                <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn tạo phiếu mượn với ' + <?php echo count($cart_items); ?> + ' cuốn sách này?')">
                    <button type="submit" name="create_borrow" class="btn btn-primary">
                        <i class="fas fa-file-invoice"></i> Tạo phiếu mượn (<?php echo count($cart_items); ?> sách)
                    </button>
                </form>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Thông tin quan trọng</h4>
                <ul>
                    <li><strong>Thời hạn mượn:</strong> Mỗi phiếu mượn có thời hạn 7 ngày kể từ ngày mượn</li>
                    <li><strong>Giới hạn mượn:</strong> Bạn có thể mượn tối đa 5 cuốn sách cùng lúc</li>
                    <li><strong>Trả sách đúng hạn:</strong> Vui lòng trả sách đúng hạn để tránh bị phạt</li>
                    <li><strong>Sách trong giỏ:</strong> Sách sẽ được giữ trong giỏ cho đến khi bạn tạo phiếu mượn</li>
                </ul>
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
        // Tự động ẩn thông báo sau 5 giây
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
        
        // Xác nhận khi xóa sách khỏi giỏ
        function confirmRemove(button) {
            if (confirm('Bạn có chắc chắn muốn xóa sách này khỏi giỏ mượn?')) {
                const removeId = button.getAttribute('data-id');
                window.location.href = '?remove=' + removeId;
            }
        }
        
        // Xác nhận tạo phiếu mượn
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const bookCount = <?php echo count($cart_items); ?>;
            if (!confirm(`Bạn có chắc chắn muốn tạo phiếu mượn với ${bookCount} cuốn sách?\n\nSau khi tạo phiếu, sách sẽ được chuyển vào danh sách sách đang mượn và không thể hủy.`)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>