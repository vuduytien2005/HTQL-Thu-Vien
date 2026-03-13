<?php
// view_book_public.php
session_start(); // THÊM DÒNG NÀY
require 'config/db.php'; // Hoặc sửa đường dẫn đúng với cấu trúc của bạn

// Lấy mã sách từ URL
$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    header('Location: index.php');
    exit();
}


// Lấy thông tin chi tiết sách
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        tl.Mo_ta as Mo_ta_the_loai
    FROM SACH s
    LEFT JOIN the_loai tl ON s.Ten_the_loai = tl.Ten_the_loai
    WHERE s.Ma_sach = ?
");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

// Kiểm tra sách có tồn tại không
if (!$book) {
    echo "<script>alert('Sách không tồn tại!'); window.location.href='../public/index.php';</script>";
    exit();
}

// Tính số bản còn lại
$so_ban_con = $book['So_ban'] - $book['So_ban_dang_muon'];
$co_the_muon = ($book['Trang_thai'] === 'Còn') && ($so_ban_con > 0);

// Lấy sách cùng thể loại (đề xuất)
$stmt = $pdo->prepare("
    SELECT Ma_sach, Ten_sach, Ten_tac_gia, Nha_xuat_ban, Trang_thai 
    FROM SACH 
    WHERE Ten_the_loai = ? AND Ma_sach != ? 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$book['Ten_the_loai'] ?? '', $book_id]);
$related_books = $stmt->fetchAll();

$page_title = $book['Ten_sach'] ?? 'Chi tiết sách';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Thư viện Sách</title>
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
        
        .login-btn {
            background-color: transparent;
            color: #1a2980;
            border-color: #1a2980ff;
        }
        
        .login-btn:hover {
            background-color: #fcfcfcff;
            color: white;
        }
        
        /* Main Content Styles */
        .book-detail-container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        
        /* Breadcrumb Navigation */
        .breadcrumb-nav {
            margin-bottom: 30px;
            padding: 15px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #64748b;
            font-size: 0.95rem;
            flex-wrap: wrap;
        }
        
        .breadcrumb a {
            color: #3b82f6;
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 500;
        }
        
        .breadcrumb a:hover {
            color: #2563eb;
            text-decoration: underline;
        }
        
        .breadcrumb .separator {
            color: #cbd5e1;
        }
        
        /* Book Header */
        .book-header {
            display: flex;
            gap: 40px;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        
        .book-cover-large {
            flex: 0 0 300px;
            height: 400px;
            background: linear-gradient(135deg, #1a2980, #26d0ce);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4.5rem;
            box-shadow: 0 15px 35px rgba(79, 109, 245, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .book-cover-large::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        
        .book-info {
            flex: 1;
            min-width: 300px;
        }
        
        .book-title {
            font-size: 2.5rem;
            color: #1e293b;
            margin-bottom: 15px;
            line-height: 1.3;
            font-weight: 700;
        }
        
        .book-subtitle {
            color: #64748b;
            font-size: 1.2rem;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .book-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            background: #f8fafc;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .meta-item i {
            color: #3b82f6;
            font-size: 1.1rem;
        }
        
        .book-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 25px;
            font-weight: 600;
            margin-top: 15px;
            font-size: 0.95rem;
        }
        
        .status-available {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.2);
        }
        
        .status-unavailable {
            background: linear-gradient(135deg, #ef4444, #f87171);
            color: white;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.2);
        }
        
        .status-discontinued {
            background: linear-gradient(135deg, #6b7280, #9ca3af);
            color: white;
            box-shadow: 0 5px 15px rgba(107, 114, 128, 0.2);
        }
        
        /* Book Actions */
        .book-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            border: none;
        }
        
        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }
        
        .btn-home {
            background: white;
            color: #475569;
            border: 2px solid #cbd5e1;
        }
        
        .btn-home:hover {
            background: #f1f5f9;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .btn-borrow {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn-borrow:hover {
            background: linear-gradient(135deg, #0da271, #10b981);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }
        
        .btn-borrow:disabled {
            background: linear-gradient(135deg, #6b7280, #9ca3af);
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .login-required-message {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            color: #856404;
            padding: 12px 20px;
            border-radius: 8px;
            margin-top: 15px;
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
        
        .book-info-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .book-quantity-info {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-top: 10px;
        }
        
        .quantity-available {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .quantity-unavailable {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .quantity-warning {
            background: #fffbeb;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        /* Book Content Section */
        .book-content-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 1.8rem;
            color: #1e293b;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3b82f6;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #3b82f6;
        }
        
        .book-description {
            line-height: 1.8;
            color: #475569;
            font-size: 1.1rem;
            white-space: pre-wrap;
            background: #f8fafc;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 30px;
        }
        
        .book-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .detail-item {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .detail-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }
        
        .detail-label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .detail-value {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        /* Related Books */
        .related-books-section {
            margin-top: 50px;
        }
        
        .related-books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }
        
        .related-book-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
        }
        
        .related-book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .related-book-cover {
            height: 160px;
            background: linear-gradient(135deg, #4f6df5, #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.2rem;
            position: relative;
            overflow: hidden;
        }
        
        .related-book-cover::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        
        .related-book-info {
            padding: 20px;
        }
        
        .related-book-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 1rem;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        
        .related-book-author {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 12px;
        }
        
        .related-book-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-related-available {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-related-unavailable {
            background: #fecaca;
            color: #991b1b;
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
            
            .book-detail-container {
                margin: 140px auto 30px;
                padding: 0 15px;
            }
            
            .book-header {
                flex-direction: column;
                padding: 25px;
                gap: 25px;
            }
            
            .book-cover-large {
                flex: none;
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
                height: 350px;
            }
            
            .book-title {
                font-size: 2rem;
            }
            
            .book-content-section {
                padding: 25px;
            }
            
            .related-books-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
            
            .book-meta {
                gap: 10px;
            }
            
            .meta-item {
                flex: 1 1 calc(50% - 10px);
                min-width: 0;
            }
            
            .book-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .book-title {
                font-size: 1.7rem;
            }
            
            .meta-item {
                flex: 1 1 100%;
            }
            
            .related-books-grid {
                grid-template-columns: 1fr;
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
                <a href="index.php">Trang chủ</a>
                <a href="index.php#books">Sách</a>
                <a href="auth/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </a>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <div class="book-detail-container">
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb-nav">
            <div class="breadcrumb">
                <a href="index.php">Trang chủ</a>
                <span class="separator">/</span>
                <a href="index.php#books">Danh sách sách</a>
                <span class="separator">/</span>
                <span><?php echo htmlspecialchars($book['Ten_sach']); ?></span>
            </div>
        </div>
        
        <!-- Book Header -->
        <div class="book-header">
           
            
            <div class="book-info">
                <h1 class="book-title"><?php echo htmlspecialchars($book['Ten_sach']); ?></h1>
                <div class="book-subtitle"><?php echo htmlspecialchars($book['Ten_tac_gia'] ?? 'Tác giả chưa rõ'); ?></div>
                
                <div class="book-meta">
                    <div class="meta-item">
                        <i class="fas fa-user-pen"></i>
                        <span>Tác giả: <?php echo htmlspecialchars($book['Ten_tac_gia'] ?? 'Không rõ'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span>Thể loại: <?php echo htmlspecialchars($book['Ten_the_loai'] ?? 'Chưa phân loại'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-building"></i>
                        <span>NXB: <?php echo htmlspecialchars($book['Nha_xuat_ban'] ?? 'Không rõ'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Năm XB: <?php echo htmlspecialchars($book['Nam_xuat_ban'] ?? 'N/A'); ?></span>
                    </div>
                </div>
                
                <?php 
                $status_class = 'status-available';
                $status_text = 'Có sẵn';
                
                if ($book['Trang_thai'] === 'Hết') {
                    $status_class = 'status-unavailable';
                    $status_text = 'Đã hết';
                } elseif ($book['Trang_thai'] === 'Ngưng sử dụng') {
                    $status_class = 'status-discontinued';
                    $status_text = 'Ngưng sử dụng';
                }
                ?>
                
                <div class="book-status <?php echo $status_class; ?>">
                    <i class="fas fa-circle"></i>
                    <?php echo $status_text; ?>
                </div>
                
                <!-- Book Actions -->
                <div class="book-actions">
                    <a href="index.php#books" class="action-btn btn-back">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                    <a href="index.php" class="action-btn btn-home">
                        <i class="fas fa-home"></i> Về trang chủ
                    </a>
                    
                    <?php if ($co_the_muon): ?>
                     <button class="action-btn btn-borrow" onclick="showLoginRequired()"> 
                        <i class="fas fa-cart-plus"></i> Mượn sách
                     </a>
                  <?php else: ?>
                     <button class="action-btn btn-borrow" disabled>
                        <i class="fas fa-times-circle"></i> Không thể mượn
                     </button>
                 <?php endif; ?>
                </div>
                <div id="loginRequiredMessage" class="login-required-message" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Vui lòng đăng nhập để mượn sách!</span>
                    <button onclick="hideLoginMessage()" style="margin-left: auto; background: none; border: none; color: #666; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Thông tin số lượng -->
                <div class="book-info-footer">
                    <?php 
                    $quantity_class = 'quantity-unavailable';
                    $quantity_text = '';
                    
                    if ($co_the_muon) {
                        if ($so_ban_con > 2) {
                            $quantity_class = 'quantity-available';
                            $quantity_text = "Còn $so_ban_con bản có sẵn để mượn";
                        } else {
                            $quantity_class = 'quantity-warning';
                            $quantity_text = "Chỉ còn $so_ban_con bản - Nhanh tay mượn nhé!";
                        }
                    } else {
                        if ($book['Trang_thai'] !== 'Còn') {
                            $quantity_text = "Sách đang " . htmlspecialchars($book['Trang_thai']);
                        } else {
                            $quantity_text = "Đã hết sách để mượn";
                        }
                    }
                    ?>
                    
                    <div class="book-quantity-info <?php echo $quantity_class; ?>">
                        <i class="fas fa-info-circle"></i>
                        <?php echo $quantity_text; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Book Content -->
        <div class="book-content-section">
            <h2 class="section-title">
                <i class="fas fa-file-lines"></i>
                Nội dung sách
            </h2>
            
            <div class="book-description">
                <?php 
                $content = $book['Noi_dung'] ?? 'Chưa có mô tả nội dung sách.';
                if (empty(trim($content))) {
                    $content = 'Sách chưa có mô tả nội dung chi tiết.';
                }
                echo nl2br(htmlspecialchars($content));
                ?>
            </div>
            
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i>
                Thông tin chi tiết
            </h2>
            
            <div class="book-details-grid">
                <div class="detail-item">
                    <div class="detail-label">Mã sách</div>
                    <div class="detail-value"><?php echo htmlspecialchars($book['Ma_sach']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Giá tiền</div>
                    <div class="detail-value"><?php echo number_format($book['Gia_tien'] ?? 0, 0, ',', '.') . ' VNĐ'; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tổng số bản</div>
                    <div class="detail-value"><?php echo $book['So_ban']; ?> bản</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Đang được mượn</div>
                    <div class="detail-value"><?php echo $book['So_ban_dang_muon']; ?> bản</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Còn lại</div>
                    <div class="detail-value"><?php echo $so_ban_con; ?> bản</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Nguồn cung cấp</div>
                    <div class="detail-value"><?php echo htmlspecialchars($book['Nguon_cung_cap'] ?? 'Không rõ'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Trạng thái</div>
                    <div class="detail-value"><?php echo htmlspecialchars($book['Trang_thai']); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Related Books -->
        <?php if (!empty($related_books)): ?>
     <div class="book-content-section">
        <div class="related-books-section">
            <h2 class="section-title">
                Sách cùng thể loại
            </h2>
            <div class="related-books-grid">
                <?php foreach ($related_books as $related_book): ?>
                <?php 
                $related_status_class = $related_book['Trang_thai'] === 'Còn' ? 'status-related-available' : 'status-related-unavailable';
                $related_status_text = $related_book['Trang_thai'] === 'Còn' ? 'Có sẵn' : 'Đã hết';
                ?>
                <a href="view_book_public.php?id=<?php echo urlencode($related_book['Ma_sach']); ?>" class="related-book-card">

                    <div class="related-book-info">
                        <div class="related-book-title">
                            <?php echo htmlspecialchars($related_book['Ten_sach']); ?>
                        </div>
                        <div class="related-book-author">
                            <?php echo htmlspecialchars($related_book['Ten_tac_gia'] ?? 'Không rõ'); ?>
                        </div>
                        <span class="related-book-status <?php echo $related_status_class; ?>">
                            <?php echo $related_status_text; ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        </div>   
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
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="index.php#books">Sách</a></li>
                    <li><a href="auth/login.php">Đăng nhập</a></li>
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
            &copy; 2025 Thư viện Sách Trực tuyến. Phiên bản công khai.
        </div>
    </footer>

    <script>

        // Thêm các hàm xử lý thông báo
    function showLoginRequired() {
        const message = document.getElementById('loginRequiredMessage');
        message.style.display = 'flex';
        message.style.animation = 'slideDown 0.3s ease';
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            hideLoginMessage();
        }, 5000);
        
        // Cuộn đến thông báo
        message.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }

    function hideLoginMessage() {
        const message = document.getElementById('loginRequiredMessage');
        message.style.opacity = '0';
        message.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            message.style.display = 'none';
            message.style.opacity = '1';
        }, 300);
    }

    // Thêm event listener cho nút mượn sách
    document.querySelector('.btn-borrow')?.addEventListener('click', function(e) {
        if (!this.disabled) {
            e.preventDefault();
            showLoginRequired();
        }
    });
    
    // Giữ lại các hàm khác
    // Hiệu ứng hover cho thẻ sách liên quan
    document.querySelectorAll('.related-book-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.08)';
        });
    });
        // Hiệu ứng hover cho thẻ sách liên quan
        document.querySelectorAll('.related-book-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
                this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.08)';
            });
        });
    </script>
</body>
</html>