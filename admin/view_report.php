<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền xem báo cáo.");
}

$report_id = $_GET['id'] ?? null;

if (!$report_id) {
    die("Không tìm thấy báo cáo.");
}

// Lấy thông tin báo cáo
$stmt = $pdo->prepare("SELECT * FROM BAO_CAO_THONG_KE WHERE Ma_bao_cao = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch();

if (!$report) {
    die("Báo cáo không tồn tại.");
}

// Giải mã dữ liệu JSON
$report_data = json_decode($report['Du_lieu'], true);

// Helper functions
function get_report_icon($type) {
    $icons = [
        'Sách' => '📚',
        'Độc giả' => '👥',
        'Mượn trả' => '🔄',
        'Doanh thu' => '💰',
        'Tổng quan' => '📊'
    ];
    return $icons[$type] ?? '📊';
}

function get_report_type_class($type) {
    $types = [
        'Sách' => 'sach',
        'Độc giả' => 'docgia',
        'Mượn trả' => 'muon-tra',
        'Doanh thu' => 'doanh-thu',
        'Tổng quan' => 'tong-quan'
    ];
    return $types[$type] ?? 'khac';
}

function format_report_value($key, $value) {
    if (is_numeric($value)) {
        $formatted = number_format($value, 0, ',', '.');
        if (strpos($key, 'tiền') !== false || strpos($key, 'phạt') !== false || strpos($key, 'doanh thu') !== false || strpos($key, 'giá') !== false) {
            return $formatted . ' VNĐ';
        }
        if (strpos($key, 'phần trăm') !== false || strpos($key, 'tỷ lệ') !== false) {
            return $formatted . '%';
        }
        return $formatted;
    }
    return htmlspecialchars($value);
}

function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = array(
        'y' => 'năm',
        'm' => 'tháng',
        'd' => 'ngày',
        'h' => 'giờ',
        'i' => 'phút',
        's' => 'giây',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ' trước';
        } else {
            unset($string[$k]);
        }
    }

    $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) : 'vừa xong';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Báo cáo #<?= $report['Ma_bao_cao'] ?> - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .report-detail {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .page-title {
            color: var(--text);
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .report-info-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }
        
        .report-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: #fbfdff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e6eef8;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
            line-height: 1;
        }
        
        .stat-label {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 8px;
        }
        
        .report-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
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
        
        .type-tong-quan {
            background: #e0f2fe;
            color: #0c4a6e;
            border: 1px solid #bae6fd;
        }
        
        .detail-table-container {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 25px;
        }
        
        .metric-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eef2f7;
            transition: background-color 0.2s ease;
        }
        
        .metric-row:hover {
            background: #f8fafc;
        }
        
        .metric-row:last-child {
            border-bottom: none;
        }
        
        .metric-label {
            font-weight: 600;
            color: var(--text);
            flex: 1;
        }
        
        .metric-value {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .metric-description {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 4px;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e6eef8;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-label {
            color: var(--muted);
            font-weight: 500;
        }
        
        .info-value {
            color: var(--text);
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            .print-only {
                display: block;
            }
            
            .action-buttons {
                display: none;
            }
            
            .report-info-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .report-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="report-detail">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <?= get_report_icon($report['Loai_bao_cao']) ?> 
                        Báo cáo #<?= $report['Ma_bao_cao'] ?>
                    </h1>
                    <p class="sub" style="color: var(--muted); margin-top: 4px;">
                        Chi tiết báo cáo thống kê hệ thống thư viện
                    </p>
                </div>
                <div class="no-print">
                    <a href="list_report.php" class="btn btn-secondary">← Danh sách báo cáo</a>
                </div>
            </div>

            <!-- Thông tin báo cáo -->
            <div class="report-info-card">
                <h3 style="margin-bottom: 20px; color: var(--text);">📋 Thông tin báo cáo</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Mã báo cáo:</span>
                        <span class="info-value">#<?= $report['Ma_bao_cao'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Loại báo cáo:</span>
                        <span class="report-type-badge type-<?= get_report_type_class($report['Loai_bao_cao']) ?>">
                            <?= get_report_icon($report['Loai_bao_cao']) ?> 
                            <?= htmlspecialchars($report['Loai_bao_cao']) ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Người tạo:</span>
                        <span class="info-value"><?= htmlspecialchars($report['Nguoi_tao']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Thời gian tạo:</span>
                        <span class="info-value">
                            <?= date('d/m/Y H:i:s', strtotime($report['Thoi_gian_tao'])) ?>
                            <br>
                            <small style="color: var(--muted);">(<?= time_elapsed_string($report['Thoi_gian_tao']) ?>)</small>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <?php if ($report_data && is_array($report_data) && count($report_data) > 3): ?>
            <div class="report-stats-grid no-print">
                <?php 
                $count = 0;
                foreach ($report_data as $key => $value): 
                    if ($count >= 4) break;
                    if (is_numeric($value) && $value > 0):
                ?>
                    <div class="stat-card">
                        <span class="stat-number"><?= format_report_value($key, $value) ?></span>
                        <span class="stat-label"><?= htmlspecialchars($key) ?></span>
                    </div>
                <?php 
                    $count++;
                    endif;
                endforeach; 
                ?>
            </div>
            <?php endif; ?>

            <!-- Chi tiết thống kê -->
            <div class="detail-table-container">
                <div style="padding: 20px; border-bottom: 1px solid #eef2f7;">
                    <h3 style="margin: 0; color: var(--text);">📈 Thống kê chi tiết</h3>
                </div>
                
                <?php if ($report_data && is_array($report_data)): ?>
                    <div>
                        <?php foreach ($report_data as $key => $value): ?>
                            <div class="metric-row">
                                <div class="metric-label">
                                    <?= htmlspecialchars($key) ?>
                                    <?php if (is_numeric($value) && $value == 0): ?>
                                        <div class="metric-description">⚠️ Không có dữ liệu</div>
                                    <?php endif; ?>
                                </div>
                                <div class="metric-value">
                                    <?= format_report_value($key, $value) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <h3 style="color: var(--muted); margin-bottom: 8px;">Không có dữ liệu</h3>
                        <p style="color: var(--muted);">Báo cáo này không chứa dữ liệu thống kê.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Các nút hành động -->
            <div class="action-buttons no-print">
                <a href="list_report.php" class="btn btn-secondary">← Quay lại danh sách</a>
                <a href="dashboard.php" class="btn" style="background: var(--accent); color: white;"> Về Dashboard</a>
            </div>

            <!-- Thông tin in ấn -->
            <div class="print-only">
                <p style="text-align: center; color: var(--muted); margin-top: 30px;">
                    Được in từ Hệ thống Quản lý Thư viện - 
                    <?= date('d/m/Y H:i:s') ?>
                </p>
            </div>
        </div>
    </div>


</body>
</html>