<?php
session_start();
require '../config/db.php';

// Thêm debug flag và bật PDO exception mode (đặt false trên production)
$debug = false;
if (isset($pdo) && $pdo instanceof PDO) {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// Hàm log lỗi: ghi vào c:\xampp\htdocs\HTQL-Thu-Vien\logs\error.log (thư mục logs nằm ở root)
function log_error($msg) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $file = $logDir . '/error.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
    @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'docgia') {
    die("Bạn không có quyền mượn sách.");
}

$ma_doc_gia = $_SESSION['user']['username']; // giả sử username trùng mã độc giả

$message = '';

// Helper: tìm cột tổng số lượng trong row nếu có
function detect_total_from_row($row) {
    $candidates = ['So_luong', 'So_ban', 'Tong_so_sach', 'So_ban_co', 'Tong_so_ban', 'So_luong_tong', 'Tong_so_luong'];
    foreach ($candidates as $col) {
        if (isset($row[$col])) {
            return intval($row[$col]);
        }
    }
    return null; // không có cột tổng
}

// Lấy danh sách sách để hiển thị dropdown
$stmt = $pdo->prepare("SELECT * FROM SACH ORDER BY Ten_sach ASC");
$stmt->execute();
$sach_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_sach = trim($_POST['ma_sach'] ?? '');
    if (empty($ma_sach)) {
        $message = "Vui lòng chọn sách cần mượn.";
    } else {
        // Lấy đầy đủ thông tin sách (SELECT * để tránh lỗi nếu cột khác tên)
        $stmt = $pdo->prepare("SELECT * FROM SACH WHERE Ma_sach = ?");
        $stmt->execute([$ma_sach]);
        $sach = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sach) {
            $message = "Không tìm thấy sách có mã: " . htmlspecialchars($ma_sach);
        } else {
            // Lấy số bản đang mượn (nếu có)
            $borrowed = isset($sach['So_ban_dang_muon']) ? intval($sach['So_ban_dang_muon']) : 0;
            $total = detect_total_from_row($sach);
            $available = ($total !== null) ? ($total - $borrowed) : null;

            if ($available !== null && $available <= 0) {
                $message = "Sách \"" . htmlspecialchars($sach['Ten_sach']) . "\" hiện đang hết hoặc đã được mượn hết.";
            } else {
                try {
                    // Bắt đầu transaction
                    $pdo->beginTransaction();

                    $ma_phieu = 'PM' . time() . rand(100,999);
                    $ngay_muon = date('Y-m-d');
                    $ngay_hen_tra = date('Y-m-d', strtotime('+7 days'));

                    // Tạo phiếu mượn
                    $stmt = $pdo->prepare("INSERT INTO PHIEU_MUON (Ma_phieu_muon, Ma_doc_gia, Ma_nhan_vien, Ngay_muon, Ngay_hen_tra, Tong_so_sach, Trang_thai)
                                           VALUES (?, ?, NULL, ?, ?, 1, 'Đang mượn')");
                    $stmt->execute([$ma_phieu, $ma_doc_gia, $ngay_muon, $ngay_hen_tra]);

                    // Chi tiết mượn
                    $stmt = $pdo->prepare("INSERT INTO CHI_TIET_MUON (Ma_phieu_muon, Ma_sach) VALUES (?, ?)");
                    $stmt->execute([$ma_phieu, $ma_sach]);

                    // Cập nhật số bản đang mượn (nếu cột So_ban_dang_muon tồn tại)
                    if (isset($sach['So_ban_dang_muon'])) {
                        $stmt = $pdo->prepare("UPDATE SACH SET So_ban_dang_muon = So_ban_dang_muon + 1 WHERE Ma_sach = ?");
                        $stmt->execute([$ma_sach]);
                    }

                    $pdo->commit();
                    $message = "✅ Đã mượn sách thành công: " . htmlspecialchars($sach['Ten_sach']) . " (Mã: " . htmlspecialchars($ma_sach) . "). Mã phiếu: $ma_phieu";
                } catch (Exception $e) {
                    // Ghi log chi tiết và hiển thị theo chế độ debug
                    $err = "Borrow action failed for user={$ma_doc_gia}, ma_sach={$ma_sach}, exception=" . $e->getMessage() . " | trace: " . $e->getTraceAsString();
                    log_error($err);

                    if ($debug) {
                        $message = "Lỗi hệ thống: " . htmlspecialchars($e->getMessage());
                    } else {
                        $message = "Không thể mượn sách do lỗi hệ thống. Quản trị sẽ kiểm tra.";
                    }

                    // Rollback an toàn nếu đang trong transaction
                    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Mượn sách</title>

    <!-- Use external stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

    <div class="header">
        <h2>Mượn sách - <?php echo htmlspecialchars($_SESSION['user']['username']); ?></h2>
        <a class="btn" href="../auth/logout.php">Đăng xuất</a>
    </div>

    <p class="back-link"><a href="dashboard.php">⬅ Quay lại trang chủ</a></p>

    <div class="card-box">
        <h3>Mượn sách</h3>

        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, '✅') === 0) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Thêm form chọn từ dropdown sách -->
        <form method="POST" class="borrow-form">
            <label for="ma_sach">Chọn sách:</label>
            <select name="ma_sach" id="ma_sach" required>
                <option value="">-- Chọn 1 sách --</option>
                <?php foreach ($sach_list as $s):
                    $borrowed = isset($s['So_ban_dang_muon']) ? intval($s['So_ban_dang_muon']) : 0;
                    $total = detect_total_from_row($s);
                    if ($total !== null) {
                        $available_display = ($total - $borrowed) . ' có sẵn';
                    } else {
                        $available_display = 'Số lượng tổng: không xác định' . ($borrowed ? ' (Đang mượn: ' . $borrowed . ')' : '');
                    }
                ?>
                    <option value="<?php echo htmlspecialchars($s['Ma_sach']); ?>">
                        <?php echo htmlspecialchars($s['Ma_sach'] . ' - ' . $s['Ten_sach'] . ' (' . $available_display . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button type="submit" class="btn btn-primary">Mượn sách</button>
        </form>
    </div>

</div>

</body>
</html>