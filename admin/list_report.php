<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền xem báo cáo.");
}

$stmt = $pdo->query("SELECT * FROM BAO_CAO_THONG_KE ORDER BY Thoi_gian_tao DESC");
$reports = $stmt->fetchAll();
?>

<h3>Danh sách báo cáo thống kê</h3>
<table border="1">
    <tr><th>ID</th><th>Loại báo cáo</th><th>Thời gian</th><th>Người tạo</th><th>Dữ liệu</th></tr>
    <?php foreach ($reports as $r): ?>
        <tr>
            <td><?= $r['Ma_bao_cao'] ?></td>
            <td><?= $r['Loai_bao_cao'] ?></td>
            <td><?= $r['Thoi_gian_tao'] ?></td>
            <td><?= $r['Nguoi_tao'] ?></td>
            <td><pre><?= json_encode(json_decode($r['Du_lieu']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre></td>
        </tr>
    <?php endforeach; ?>
</table>
    <br>
    <a href="dashboard.php">← Quay lại Dashboard</a>