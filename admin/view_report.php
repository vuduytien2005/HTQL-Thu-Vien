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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem báo cáo - Quản trị Thư viện</title>
</head>
<body>
    <div>
        <h1>Chi tiết báo cáo</h1>
        
        <!-- Thông tin chung về báo cáo -->
        <div>
            <h3>Thông tin báo cáo</h3>
            <table border="1">
                <tr>
                    <th>Mã báo cáo</th>
                    <th>Loại báo cáo</th>
                    <th>Người tạo</th>
                    <th>Thời gian tạo</th>
                </tr>
                <tr>
                    <td><?= $report['Ma_bao_cao'] ?></td>
                    <td><?= $report['Loai_bao_cao'] ?></td>
                    <td><?= $report['Nguoi_tao'] ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($report['Thoi_gian_tao'])) ?></td>
                </tr>
            </table>
        </div>

        <!-- Thống kê chi tiết -->
        <div>
            <h3>Thống kê chi tiết</h3>
            
            <?php if ($report_data && is_array($report_data)): ?>
                <table border="1">
                    <tr>
                        <th>Chỉ số</th>
                        <th>Giá trị</th>
                    </tr>
                    <?php foreach ($report_data as $key => $value): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($key) ?></strong></td>
                            <td>
                                <?php if (is_numeric($value)): ?>
                                    <?= number_format($value, 0, ',', '.') ?>
                                    <?php if (strpos($key, 'tiền') !== false || strpos($key, 'phạt') !== false): ?>
                                        VNĐ
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($value) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>Không có dữ liệu thống kê trong báo cáo này.</p>
            <?php endif; ?>
        </div>

        <!-- Các nút hành động -->
        <div>
            <br>
            <a href="list_report.php">← Quay lại danh sách báo cáo</a>
            &nbsp;|&nbsp;
            <a href="dashboard.php">← Quay về Dashboard</a>
            &nbsp;|&nbsp;
            <button onclick="window.print()">In báo cáo</button>
        </div>
    </div>
</body>
</html>