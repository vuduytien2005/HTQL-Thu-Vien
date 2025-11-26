<?php
session_start();
require '../config/db.php';

// Kiểm tra quyền admin
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$ma_sach = $_GET['ma_sach'] ?? null;

// Lấy thông tin sách để hiển thị
if ($ma_sach) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM SACH WHERE Ma_sach = ?");
        $stmt->execute([$ma_sach]);
        $book = $stmt->fetch();
        
        if (!$book) {
            header('Location: list_book.php?error=Không tìm thấy sách');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: list_book.php?error=Lỗi hệ thống');
        exit();
    }
} else {
    header('Location: list_book.php?error=Không tìm thấy mã sách');
    exit();
}

// Xử lý khi submit form xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // BẮT ĐẦU TRANSACTION
        $pdo->beginTransaction();

        // 1. KIỂM TRA XEM CÓ THỂ LOẠI LIÊN QUAN KHÔNG
        $check_categories = $pdo->prepare("SELECT COUNT(*) FROM sach_the_loai WHERE Ma_sach = ?");
        $check_categories->execute([$ma_sach]);
        $has_categories = $check_categories->fetchColumn() > 0;

        // 3. XÓA DỮ LIỆU LIÊN QUAN TRONG BẢNG sach_the_loai TRƯỚC
        if ($has_categories) {
            $delete_categories_stmt = $pdo->prepare("DELETE FROM sach_the_loai WHERE Ma_sach = ?");
            $delete_categories_stmt->execute([$ma_sach]);
        }


        

        // 5. CUỐI CÙNG MỚI XÓA SÁCH
        $stmt = $pdo->prepare("DELETE FROM SACH WHERE Ma_sach = ?");
        $stmt->execute([$ma_sach]);
        
        // COMMIT TRANSACTION
        $pdo->commit();
        
        header('Location: list_book.php?success=Đã xóa sách: ' . urlencode($book['Ten_sach']));
        exit();
    } catch (PDOException $e) {
        // ROLLBACK NẾU CÓ LỖI
        $pdo->rollBack();
        
        // Hiển thị thông báo lỗi cụ thể
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            $error = "Không thể xóa sách vì vẫn còn dữ liệu liên quan. Vui lòng kiểm tra lại các phiếu mượn hoặc liên hệ quản trị viên.";
        } else {
            $error = "Lỗi khi xóa sách: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận xóa sách - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* === DELETE BOOK PAGE STYLES === */
        :root {
            --primary: #106db1;
            --accent: #0ea5a9;
            --danger: #e11d48;
            --muted: #6b7280;
            --card-bg: #ffffff;
        }

        body.delete-bg {
            background-image: linear-gradient(rgba(5, 30, 40, 0.35), rgba(5, 30, 40, 0.35)), url('https://4kwallpapers.com/images/walls/thumbs_3t/14889.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .delete-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 30px rgba(16, 24, 40, 0.18);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.45);
            text-align: center;
        }

        .delete-icon {
            font-size: 3.5rem;
            color: var(--danger);
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .delete-title {
            color: var(--danger);
            font-size: 1.6rem;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .delete-subtitle {
            color: var(--muted);
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .book-info {
            background: #fbfdff;
            border: 1px solid #e6eef8;
            border-left: 4px solid var(--danger);
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
            text-align: left;
        }

        .book-info h4 {
            color: var(--danger);
            margin-bottom: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            border-bottom: 1px solid #eef2f7;
            padding-bottom: 8px;
        }

        .book-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
            padding: 4px 0;
        }

        .book-detail strong {
            color: var(--text);
            min-width: 120px;
            font-weight: 600;
        }

        .book-detail span {
            color: var(--muted);
            text-align: right;
            flex: 1;
        }

        .warning-message {
            background: #fff8f1;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
        }

        .warning-message h4 {
            color: #9a3412;
            margin-bottom: 12px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .warning-message ul {
            margin-left: 20px;
            color: #9a3412;
        }

        .warning-message li {
            margin-bottom: 8px;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .delete-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: 1rem;
            min-width: 140px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #c0392b);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--muted), #7f8c8d);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #7f8c8d, #6c7a7d);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.4);
        }

        .book-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 8px;
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

        .loading {
            display: none;
            margin-top: 20px;
            text-align: center;
        }

        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid var(--danger);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .message.error {
            background: #fee;
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: left;
        }

        .dependencies-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            text-align: left;
        }

        .dependencies-info h5 {
            color: #0c4a6e;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .dependencies-list {
            color: #0c4a6e;
            font-size: 0.85rem;
            margin-left: 15px;
        }

        @media (max-width: 768px) {
            .delete-card {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .delete-actions {
                flex-direction: column;
            }
            
            .btn-lg {
                width: 100%;
            }
            
            .book-detail {
                flex-direction: column;
                gap: 4px;
            }
            
            .book-detail strong {
                min-width: auto;
            }
            
            .book-detail span {
                text-align: left;
            }
        }
    </style>
</head>
<body class="delete-bg">
    <div class="auth-center">
        <div class="delete-card">
            <div class="delete-icon">⚠️</div>
            <h2 class="delete-title">Xác nhận xóa sách</h2>
            <p class="delete-subtitle">Bạn sắp xóa một cuốn sách khỏi hệ thống thư viện</p>
            
            <!-- Hiển thị thông báo lỗi nếu có -->
            <?php if (!empty($error)): ?>
                <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="book-info">
                <h4>📖 Thông tin sách sẽ bị xóa</h4>
                <div class="book-detail">
                    <strong>Mã sách:</strong>
                    <span><?php echo htmlspecialchars($book['Ma_sach']); ?></span>
                </div>
                <div class="book-detail">
                    <strong>Tên sách:</strong>
                    <span><?php echo htmlspecialchars($book['Ten_sach']); ?></span>
                </div>
                <div class="book-detail">
                    <strong>Tác giả:</strong>
                    <span><?php echo htmlspecialchars($book['Ten_tac_gia']); ?></span>
                </div>
                <div class="book-detail">
                    <strong>Nhà xuất bản:</strong>
                    <span><?php echo htmlspecialchars($book['Nha_xuat_ban']); ?></span>
                </div>
                <div class="book-detail">
                    <strong>Năm xuất bản:</strong>
                    <span><?php echo htmlspecialchars($book['Nam_xuat_ban']); ?></span>
                </div>
                <div class="book-detail">
                    <strong>Giá tiền:</strong>
                    <span><?php echo number_format($book['Gia_tien'], 0, ',', '.') . ' VNĐ'; ?></span>
                </div>
                <div class="book-detail">
                    <strong>Số bản:</strong>
                    <span><?php echo htmlspecialchars($book['So_ban']); ?></span>
                </div>
                <div class="book-detail">
                    <strong>Trạng thái:</strong>
                    <span>
                        <?php echo htmlspecialchars($book['Trang_thai']); ?>
                        <span class="book-status <?php echo $book['Trang_thai'] === 'Còn' ? 'status-available' : 'status-unavailable'; ?>">
                            <?php echo $book['Trang_thai'] === 'Còn' ? 'Còn' : 'Hết'; ?>
                        </span>
                    </span>
                </div>
            </div>

            <!-- Thông tin phụ thuộc -->
            <div class="dependencies-info">
                <h5>📋 Dữ liệu sẽ bị xóa cùng:</h5>
                <ul class="dependencies-list">
                    <li>Thông tin thể loại của sách</li>
                    <li>Lịch sử mượn/trả liên quan đến sách</li>
                    <li>Tất cả dữ liệu thống kê liên quan</li>
                </ul>
            </div>

            <div class="warning-message">
                <h4>⚠️ Cảnh báo quan trọng</h4>
                <ul>
                    <li><strong>Hành động này không thể hoàn tác</strong> - Dữ liệu sẽ bị xóa vĩnh viễn</li>
                    <li><strong>Ảnh hưởng đến hệ thống</strong> - Tất cả dữ liệu liên quan đến sách sẽ bị xóa</li>
                    <li><strong>Ảnh hưởng đến thống kê</strong> - Sẽ ảnh hưởng đến thống kê và báo cáo hệ thống</li>
                </ul>
            </div>

            <div class="delete-actions">
                <form method="POST" onsubmit="showLoading()">
                    <button type="submit" class="btn-lg btn-danger">🗑️ Xóa sách</button>
                </form>
                <a href="list_book.php" class="btn-lg btn-secondary">↩️ Quay lại</a>
            </div>

            <div class="loading" id="loading">
                <div class="loading-spinner"></div>
                <p>Đang xóa sách và dữ liệu liên quan...</p>
            </div>
        </div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
            document.querySelector('button[type="submit"]').disabled = true;
            document.querySelector('button[type="submit"]').innerHTML = '⏳ Đang xóa...';
        }
    </script>
</body>
</html>