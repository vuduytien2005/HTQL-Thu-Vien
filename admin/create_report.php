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
    $nguoi_tao = $_SESSION['user']['username'];

    // Tự động lấy dữ liệu thống kê từ database
    $total_books = $pdo->query("SELECT COUNT(*) FROM SACH")->fetchColumn();
    $total_readers = $pdo->query("SELECT COUNT(*) FROM DOC_GIA")->fetchColumn();
    $total_borrows = $pdo->query("SELECT COUNT(*) FROM PHIEU_MUON")->fetchColumn();
    $total_fines = $pdo->query("SELECT SUM(Tien_phat) FROM CHI_TIET_MUON")->fetchColumn();

    // Tạo dữ liệu JSON
    $du_lieu = json_encode([
        'Tổng số sách' => $total_books,
        'Tổng số độc giả' => $total_readers,
        'Tổng số lượt mượn' => $total_borrows,
        'Tổng tiền phạt' => $total_fines ?? 0,
        'Ghi chú' => $ghi_chu
    ], JSON_UNESCAPED_UNICODE);

    try {
        $stmt = $pdo->prepare("INSERT INTO BAO_CAO_THONG_KE (Loai_bao_cao, Nguoi_tao, Du_lieu) VALUES (?, ?, ?)");
        $stmt->execute([$loai_bao_cao, $nguoi_tao, $du_lieu]);
        $message = "✅ Đã tạo báo cáo thống kê thành công!";
    } catch (Exception $e) {
        $message = "❌ Lỗi khi tạo báo cáo: " . $e->getMessage();
    }
}

// Lấy dữ liệu hiện tại để hiển thị
$total_books = $pdo->query("SELECT COUNT(*) FROM SACH")->fetchColumn();
$total_readers = $pdo->query("SELECT COUNT(*) FROM DOC_GIA")->fetchColumn();
$total_borrows = $pdo->query("SELECT COUNT(*) FROM PHIEU_MUON")->fetchColumn();
$total_fines = $pdo->query("SELECT SUM(Tien_phat) FROM CHI_TIET_MUON")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo báo cáo thống kê - Quản trị Thư viện</title>
</head>
<body>
    <h1>Tạo báo cáo thống kê</h1>
    
    <?php if (!empty($message)): ?>
        <div style="padding: 10px; margin: 10px 0; border: 1px solid <?php echo strpos($message, '✅') !== false ? '#c3e6cb' : '#f5c6cb'; ?>; background: <?php echo strpos($message, '✅') !== false ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo strpos($message, '✅') !== false ? '#155724' : '#721c24'; ?>;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Hiển thị thống kê hiện tại -->
    <div style="background: #f8f9fa; padding: 15px; margin: 15px 0; border: 1px solid #dee2e6;">
        <h3>Thống kê hiện tại:</h3>
        <p><strong>Tổng số sách:</strong> <?= number_format($total_books) ?> cuốn</p>
        <p><strong>Tổng số độc giả:</strong> <?= number_format($total_readers) ?> người</p>
        <p><strong>Tổng số lượt mượn:</strong> <?= number_format($total_borrows) ?> lượt</p>
        <p><strong>Tổng tiền phạt:</strong> <?= number_format($total_fines ?? 0) ?> VNĐ</p>
    </div>

    <form method="POST">
        <div>
            <label><strong>Loại báo cáo:</strong></label><br>
            <input type="text" name="loai_bao_cao" placeholder="Ví dụ: Thống kê tổng quan tháng 12/2024" style="width: 400px;" required>
        </div>

        <br>

        <div>
            <label><strong>Ghi chú:</strong></label><br>
            <textarea name="ghi_chu" placeholder="Nhập ghi chú cho báo cáo..." rows="4" style="width: 400px;"></textarea>
        </div>

        <br>

        <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">
            Tạo báo cáo
        </button>
    </form>

    <br>
    <a href="list_report.php">📋 Xem danh sách báo cáo</a> | 
    <a href="dashboard.php">← Quay lại Dashboard</a>
</body>
</html>