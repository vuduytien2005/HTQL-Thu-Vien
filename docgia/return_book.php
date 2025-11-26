<?php
session_start();
require '../config/db.php';

// Kiểm tra quyền
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'docgia') {
    die("Bạn không có quyền truy cập trang này.");
}

$ma_doc_gia = $_SESSION['user']['username'];
$message = '';

// Xử lý trả sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ma_phieu']) && isset($_POST['ma_sach'])) {
    $ma_phieu = $_POST['ma_phieu'];
    $ma_sach = $_POST['ma_sach'];

    try {
        $pdo->beginTransaction();

        // Giảm So_ban_dang_muon (nhận đảm bảo không âm)
        $stmt = $pdo->prepare("UPDATE SACH SET So_ban_dang_muon = CASE WHEN So_ban_dang_muon > 0 THEN So_ban_dang_muon - 1 ELSE 0 END WHERE Ma_sach = ?");
        $stmt->execute([$ma_sach]);

        // Cập nhật trạng thái phiếu mượn thành 'Đã trả'
        $stmt = $pdo->prepare("UPDATE PHIEU_MUON SET Trang_thai = 'Đã trả' WHERE Ma_phieu_muon = ? AND Ma_doc_gia = ?");
        $stmt->execute([$ma_phieu, $ma_doc_gia]);

        $pdo->commit();
        $message = "✅ Đã trả sách (Mã sách: " . htmlspecialchars($ma_sach) . ").";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Không thể trả sách do lỗi hệ thống. Vui lòng thử lại.";
    }
}

// Lấy danh sách sách đang mượn của độc giả
$stmt = $pdo->prepare("
    SELECT pm.Ma_phieu_muon, s.Ma_sach, s.Ten_sach, pm.Ngay_muon, pm.Ngay_hen_tra
    FROM PHIEU_MUON pm
    JOIN CHI_TIET_MUON c ON pm.Ma_phieu_muon = c.Ma_phieu_muon
    JOIN SACH s ON c.Ma_sach = s.Ma_sach
    WHERE pm.Ma_doc_gia = ? AND pm.Trang_thai = 'Đang mượn'
    ORDER BY pm.Ngay_muon DESC
");
$stmt->execute([$ma_doc_gia]);
$borrowed = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trả sách</title>

    <!-- Use external stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Trả sách - <?php echo htmlspecialchars($_SESSION['user']['username']); ?></h2>
        <a class="btn" href="../auth/logout.php">Đăng xuất</a>
    </div>

    <p class="back-link"><a href="dashboard.php">⬅ Quay lại trang chủ</a></p>

    <div class="card-box">
        <h3>Trả sách</h3>

        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, '✅') === 0) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($borrowed)): ?>
            <p>Bạn hiện không có phiếu mượn nào đang hoạt động.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Mã sách</th>
                        <th>Tên sách</th>
                        <th>Ngày mượn</th>
                        <th>Ngày hẹn trả</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrowed as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['Ma_phieu_muon']); ?></td>
                            <td><?php echo htmlspecialchars($b['Ma_sach']); ?></td>
                            <td><?php echo htmlspecialchars($b['Ten_sach']); ?></td>
                            <td><?php echo htmlspecialchars($b['Ngay_muon']); ?></td>
                            <td><?php echo htmlspecialchars($b['Ngay_hen_tra']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="ma_phieu" value="<?php echo htmlspecialchars($b['Ma_phieu_muon']); ?>">
                                    <input type="hidden" name="ma_sach" value="<?php echo htmlspecialchars($b['Ma_sach']); ?>">
                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Bạn có chắc chắn muốn trả sách này không?');">Trả sách</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>