<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền tạo báo cáo.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loai_bao_cao = $_POST['loai_bao_cao'];
    $ghi_chu = $_POST['ghi_chu'];
    
    // Lấy username từ session
    $nguoi_tao = $_SESSION['user']['username'];

    // Tự động lấy dữ liệu thống kê từ database
    $total_books = $pdo->query("SELECT COUNT(*) FROM SACH")->fetchColumn();
    $total_readers = $pdo->query("SELECT COUNT(*) FROM DOC_GIA")->fetchColumn();
    
    // Kiểm tra xem bảng PHIEU_MUON có tồn tại không
    try {
        $total_borrows = $pdo->query("SELECT COUNT(*) FROM phieu_muon")->fetchColumn();
    } catch (Exception $e) {
        $total_borrows = 0;
    }

    // Tạo dữ liệu JSON
    $du_lieu = json_encode([
        'Tổng số sách' => (int)$total_books,
        'Tổng số độc giả' => (int)$total_readers,
        'Tổng số lượt mượn' => (int)$total_borrows,
        'Ghi chú' => $ghi_chu
    ], JSON_UNESCAPED_UNICODE);

    try {
        $stmt = $pdo->prepare("INSERT INTO bao_cao_thong_ke (Loai_bao_cao, Nguoi_tao, Du_lieu) VALUES (?, ?, ?)");
        $stmt->execute([$loai_bao_cao, $nguoi_tao, $du_lieu]);
        $message = "✅ Đã tạo báo cáo thống kê thành công!";
    } catch (Exception $e) {
        $message = "❌ Lỗi khi tạo báo cáo: " . $e->getMessage();
    }
}

// Lấy dữ liệu hiện tại để hiển thị
$total_books = $pdo->query("SELECT COUNT(*) FROM SACH")->fetchColumn();
$total_readers = $pdo->query("SELECT COUNT(*) FROM DOC_GIA")->fetchColumn();

try {
    $total_borrows = $pdo->query("SELECT COUNT(*) FROM phieu_muon")->fetchColumn();
} catch (Exception $e) {
    $total_borrows = 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo báo cáo thống kê - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .report-creation {
            max-width: 800px;
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
            color: var(--text);
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .stats-preview {
            background: #fbfdff;
            border: 1px solid #e6eef8;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }
        
        .stat-label {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .form-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text);
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e6eef8;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 109, 177, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e6eef8;
        }
        
        .message {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
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
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger);
        }
        
        .help-text {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 4px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="report-creation">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">📊 Tạo Báo cáo Thống kê</h1>
                    <p class="sub" style="color: var(--muted); margin-top: 4px;">
                        Tạo báo cáo thống kê tổng quan hệ thống thư viện
                    </p>
                </div>
                <div>
                    <a href="list_report.php" class="btn btn-secondary">📋 Danh sách báo cáo</a>
                </div>
            </div>

            <!-- Thông báo -->
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Thống kê hiện tại -->
            <div class="stats-preview">
                <h3 style="color: var(--primary); margin-bottom: 15px;">📈 Thống kê hiện tại</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($total_books) ?></span>
                        <span class="stat-label">Tổng số sách</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($total_readers) ?></span>
                        <span class="stat-label">Tổng độc giả</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($total_borrows) ?></span>
                        <span class="stat-label">Lượt mượn</span>
                    </div>
                </div>
            </div>

            <!-- Form tạo báo cáo -->
            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="loai_bao_cao" class="required-field">Loại báo cáo</label>
                        <input type="text" id="loai_bao_cao" name="loai_bao_cao" 
                               placeholder="Ví dụ: Thống kê tổng quan tháng 12/2024" required>
                        <div class="help-text">Đặt tên cho báo cáo để dễ dàng nhận diện</div>
                    </div>

                    <div class="form-group">
                        <label for="ghi_chu">Ghi chú</label>
                        <textarea id="ghi_chu" name="ghi_chu" 
                                  placeholder="Nhập ghi chú, mô tả hoặc các lưu ý cho báo cáo..." 
                                  rows="4"></textarea>
                        <div class="help-text">Thông tin bổ sung cho báo cáo (không bắt buộc)</div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"> Tạo báo cáo</button>
                        <a href="dashboard.php" class="btn btn-secondary">← Quay lại Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>