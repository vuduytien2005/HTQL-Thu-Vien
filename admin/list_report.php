<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền xem báo cáo.");
}

$stmt = $pdo->query("SELECT * FROM BAO_CAO_THONG_KE ORDER BY Thoi_gian_tao DESC");
$reports = $stmt->fetchAll();

// Thống kê nhanh - ĐÃ SỬA LỖI
$total_reports = count($reports);
$report_types = [];
if (!empty($reports)) {
    $report_types = array_count_values(array_column($reports, 'Loai_bao_cao'));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo Thống kê - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .reports-management {
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
            color: var(--text);
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
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
            padding: 20px;
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
        
        .table-container {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-top: 20px;
        }
        
        .report-type-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .type-sach {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        
        .type-docgia {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .type-muon-tra {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        .type-doanh-thu {
            background: #fae8ff;
            color: #86198f;
            border: 1px solid #f0abfc;
        }
        
        .type-khac {
            background: #e5e7eb;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .data-preview {
            max-width: 300px;
            max-height: 100px;
            overflow: hidden;
            font-size: 0.8rem;
            color: var(--muted);
            background: #f8fafc;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .data-preview:hover {
            background: #f1f5f9;
        }
        
        .data-preview.expanded {
            max-height: none;
            overflow: auto;
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
        }
        
        .btn-view {
            background: var(--accent);
            color: white;
        }
        
        .btn-export {
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
        
        .btn-export:hover {
            background: #0d5a9d;
            text-decoration: none;
        }
        
        .btn-delete:hover {
            background: #c81e4a;
            text-decoration: none;
        }
        
        .time-ago {
            font-size: 0.8rem;
            color: var(--muted);
            margin-top: 4px;
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
        
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 1000px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reports-management">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">📊 Báo cáo Thống kê</h1>
                    <p class="sub" style="color: var(--muted); margin-top: 4px;">
                        Quản lý và xem tất cả báo cáo thống kê hệ thống
                    </p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary">← Quay lại Dashboard</a>
                    <a href="create_report.php" class="btn btn-primary">📈 Tạo báo cáo mới</a>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="stats-card">
                <h3 style="margin-bottom: 16px; color: var(--text);">📈 Tổng quan báo cáo</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= $total_reports ?></span>
                        <span class="stat-label">Tổng số báo cáo</span>
                    </div>
                    <?php foreach ($report_types as $type => $count): ?>
                        <div class="stat-item">
                            <span class="stat-number"><?= $count ?></span>
                            <span class="stat-label">Báo cáo <?= htmlspecialchars($type) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?= $total_reports > 0 ? date('d/m/Y', strtotime($reports[0]['Thoi_gian_tao'])) : '--' ?>
                        </span>
                        <span class="stat-label">Báo cáo mới nhất</span>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách báo cáo -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã báo cáo</th>
                            <th>Loại báo cáo</th>
                            <th>Thời gian tạo</th>
                            <th>Người tạo</th>
                            <th>Dữ liệu</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $r): 
                            $data = json_decode($r['Du_lieu'], true);
                            // Kiểm tra lỗi JSON
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $data = ['error' => 'Dữ liệu không hợp lệ'];
                            }
                            $time_ago = time_elapsed_string($r['Thoi_gian_tao']);
                        ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary);">#<?= $r['Ma_bao_cao'] ?></strong>
                                </td>
                                <td>
                                    <span class="report-type-badge type-<?= get_report_type_class($r['Loai_bao_cao']) ?>">
                                        <?= get_report_icon($r['Loai_bao_cao']) ?> <?= htmlspecialchars($r['Loai_bao_cao']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?= date('d/m/Y H:i', strtotime($r['Thoi_gian_tao'])) ?></div>
                                    <div class="time-ago"><?= $time_ago ?></div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($r['Nguoi_tao']) ?></strong>
                                </td>
                                <td>
                                    <div class="data-preview" onclick="this.classList.toggle('expanded')">
                                        <pre style="margin: 0; font-size: 0.75rem;"><?= htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_report.php?id=<?= $r['Ma_bao_cao'] ?>" 
                                           class="btn-sm btn-view" 
                                           title="Xem chi tiết báo cáo">
                                           👁️ Xem
                                        </a>
                                        <a href="export_report.php?id=<?= $r['Ma_bao_cao'] ?>" 
                                           class="btn-sm btn-export"
                                           title="Xuất báo cáo">
                                           📥 Xuất
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($reports)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">📊</div>
                                        <h3 style="color: var(--muted); margin-bottom: 8px;">Chưa có báo cáo nào</h3>
                                        <p style="color: var(--muted); margin-bottom: 16px;">Hệ thống chưa có báo cáo thống kê nào được tạo.</p>
                                       
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Toggle expand/collapse for data preview
        document.querySelectorAll('.data-preview').forEach(preview => {
            preview.addEventListener('click', function() {
                this.classList.toggle('expanded');
            });
        });
    </script>
</body>
</html>

<?php
// Helper functions - ĐÃ SỬA LỖI HÀM time_elapsed_string()
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Tính tuần từ số ngày
    $weeks = floor($diff->d / 7);
    $days = $diff->d % 7;
    
    $string = array(
        'y' => $diff->y,
        'm' => $diff->m,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    );
    
    $labels = array(
        'y' => 'năm',
        'm' => 'tháng',
        'd' => 'ngày',
        'h' => 'giờ',
        'i' => 'phút',
        's' => 'giây',
    );
    
    $result = array();
    foreach ($string as $key => $value) {
        if ($value > 0) {
            $result[] = $value . ' ' . $labels[$key] . ' trước';
        }
    }

    if (!$full && !empty($result)) {
        $result = array_slice($result, 0, 1);
    }
    
    return !empty($result) ? implode(', ', $result) : 'vừa xong';
}

function get_report_type_class($type) {
    $types = [
        'Sách' => 'sach',
        'Độc giả' => 'docgia', 
        'Mượn trả' => 'muon-tra',
        'Doanh thu' => 'doanh-thu'
    ];
    return $types[$type] ?? 'khac';
}

function get_report_icon($type) {
    $icons = [
        'Sách' => '📚',
        'Độc giả' => '👥',
        'Mượn trả' => '🔄', 
        'Doanh thu' => '💰'
    ];
    return $icons[$type] ?? '📊';
}
?>