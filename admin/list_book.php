<?php
session_start();
require '../config/db.php';

// Nếu người dùng chưa đăng nhập -> chuyển đến login
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Cho phép admin hoặc docgia truy cập; những role khác bị từ chối
$role = $_SESSION['user']['role'] ?? '';
if ($role !== 'admin' && $role !== 'docgia') {
    die("Bạn không có quyền truy cập trang này.");
}

// Xác định nhanh nếu người dùng hiện tại là admin
$isAdmin = ($role === 'admin');

// Lấy danh sách sách - không cần JOIN vì thể loại đã có sẵn trong bảng SACH
$stmt = $pdo->query("
    SELECT s.*
    FROM SACH s
    ORDER BY s.Ma_sach DESC
");
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Sách - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .books-management {
            max-width: 1400px;
            margin: 0 auto;
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
        
        .btn-view {
            background: var(--accent);
            color: white;
        }
        
        .btn-edit {
            background: var(--primary);
            color: white;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .btn-view:hover {
            background: #0d8c8f;
            text-decoration: none;
        }
        
        .btn-edit:hover {
            background: #0d5a9d;
            text-decoration: none;
        }
        
        .btn-delete:hover {
            background: #c81e4a;
            text-decoration: none;
        }
        
        .table-container {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-top: 20px;
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
        
        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .book-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
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
        
        .categories-list {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 4px;
        }
        
        .book-title {
            font-weight: 600;
            color: var(--text);
        }
        
        .book-author {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 2px;
        }
        
        .stats-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        
        .stat-item {
            text-align: center;
            padding: 16px;
            background: #fbfdff;
            border-radius: 8px;
            border: 1px solid #e6eef8;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }
        
        .stat-label {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 4px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-sm {
                text-align: center;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .book-content {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--muted);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="books-management">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Danh sách Sách</h1>
                    <p class="sub" style="color: var(--muted); margin-top: 4px;">
                        Quản lý toàn bộ sách trong thư viện 
                        <?php if (!$isAdmin): ?>
                            - Chế độ xem
                        <?php endif; ?>
                    </p>
                </div>
                <div class="header-actions">
                    <a href="../admin/dashboard.php" class="btn btn-secondary">← Trang chủ</a>
                    <?php if ($isAdmin): ?>
                        <a href="add_book.php" class="btn btn-primary">Thêm sách mới</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="stats-card">
                <h3 style="margin-bottom: 16px; color: var(--text);">Thống kê nhanh</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($books); ?></span>
                        <span class="stat-label">Tổng số sách</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php echo count(array_filter($books, function($book) { return $book['Trang_thai'] === 'Còn'; })); ?>
                        </span>
                        <span class="stat-label">Sách có sẵn</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php echo count(array_filter($books, function($book) { return $book['Trang_thai'] === 'Hết'; })); ?>
                        </span>
                        <span class="stat-label">Sách đã hết</span>
                    </div>
                    <?php if ($isAdmin): ?>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php 
                            $totalCopies = 0;
                            foreach ($books as $book) {
                                $totalCopies += (int)$book['So_ban'];
                            }
                            echo $totalCopies;
                            ?>
                        </span>
                        <span class="stat-label">Tổng số bản</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bảng danh sách sách -->
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
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary);"><?php echo htmlspecialchars($book['Ma_sach']); ?></strong>
                                </td>
                                <td>
                                    <div class="book-title"><?php echo htmlspecialchars($book['Ten_sach']); ?></div>
                                    <div class="book-author"><?php echo htmlspecialchars($book['Ten_tac_gia']); ?></div>
                                </td>
                                <td>
                                    <div class="book-content" title="<?php echo htmlspecialchars($book['Noi_dung']); ?>">
                                        <?php 
                                        $content = $book['Noi_dung'];
                                        if (strlen($content) > 50) {
                                            echo htmlspecialchars(substr($content, 0, 50)) . '...';
                                        } else {
                                            echo htmlspecialchars($content);
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($book['Ten_the_loai'])): ?>
                                        <div class="categories-list">
                                            <?php echo htmlspecialchars($book['Ten_the_loai']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--muted); font-size: 0.85rem;">Chưa phân loại</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($book['Nha_xuat_ban']); ?></td>
                                <td>
                                    <strong style="color: var(--primary);"><?php echo $book['So_ban']; ?></strong>
                                    <?php if ($isAdmin && $book['So_ban'] <= 2): ?>
                                        <br><small style="color: var(--danger);">⚠️ Sắp hết</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="book-status <?php echo $book['Trang_thai'] === 'Còn' ? 'status-available' : 'status-unavailable'; ?>">
                                        <?php echo $book['Trang_thai'] === 'Còn' ? 'Có sẵn' : 'Đã hết'; ?>
                                    </span>
                                </td>
                                <td>
                                 <div class="action-buttons">
                                  <?php if ($isAdmin): ?>
                                      <a href="update_book.php?ma_sach=<?php echo urlencode($book['Ma_sach']); ?>" 
                                         class="btn-sm btn-edit" title="Sửa thông tin">
                                         Sửa
                                     </a>
                                      <a href="delete_book.php?ma_sach=<?php echo urlencode($book['Ma_sach']); ?>" 
                                         class="btn-sm btn-delete" 
                                         onclick="return confirm('Bạn có chắc chắn muốn xóa sách \'<?php echo htmlspecialchars(addslashes($book['Ten_sach']), ENT_QUOTES); ?>\'?')"
                                         title="Xóa sách">
                                         Xóa
                                     </a>
                                 <?php else: ?>
                                     <span style="color: var(--muted); font-size: 0.8rem;">Chỉ xem</span>
                                     <?php endif; ?>
                                 </div>
                                </td>
                                
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($books)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">📚</div>
                                        <h3 style="color: var(--muted); margin-bottom: 8px;">Không có sách nào</h3>
                                        <p style="color: var(--muted); margin-bottom: 16px;">Thư viện hiện chưa có sách nào trong hệ thống.</p>
                                        <?php if ($isAdmin): ?>
                                            <a href="add_book.php" class="btn btn-primary">Thêm sách đầu tiên</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>