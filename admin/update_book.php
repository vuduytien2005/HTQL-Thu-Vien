<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền cập nhật sách.");
}

$ma_sach = $_GET['ma_sach'] ?? null;

if (!$ma_sach) {
    die("Mã sách không hợp lệ.");
}

// Lấy danh sách nhà cung cấp
$suppliers = $pdo->query("SELECT * FROM nha_cung_cap")->fetchAll();

// Lấy danh sách thể loại
$categories = $pdo->query("SELECT * FROM the_loai")->fetchAll();

// Lấy thể loại hiện tại của sách
$current_categories_stmt = $pdo->prepare("SELECT Ma_the_loai FROM sach_the_loai WHERE Ma_sach = ?");
$current_categories_stmt->execute([$ma_sach]);
$current_categories = $current_categories_stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_sach = $_POST['ten_sach'];
    $ten_tac_gia = $_POST['ten_tac_gia'];
    $nam_xuat_ban = $_POST['nam_xuat_ban'];
    $nha_xuat_ban = $_POST['nha_xuat_ban'];
    $gia_tien = $_POST['gia_tien'];
    $so_ban = $_POST['so_ban'];
    $nguon_cung_cap = $_POST['nguon_cung_cap'];
    $trang_thai = $_POST['trang_thai'];
    $the_loai = $_POST['the_loai'] ?? [];

    try {
        $pdo->beginTransaction();

        // Cập nhật thông tin sách
        $stmt = $pdo->prepare("UPDATE SACH SET 
            Ten_sach = ?, 
            Ten_tac_gia = ?, 
            Nam_xuat_ban = ?, 
            Nha_xuat_ban = ?, 
            Gia_tien = ?, 
            So_ban = ?, 
            Nguon_cung_cap = ?, 
            Trang_thai = ? 
            WHERE Ma_sach = ?");
        
        $stmt->execute([$ten_sach, $ten_tac_gia, $nam_xuat_ban, $nha_xuat_ban, $gia_tien, $so_ban, $nguon_cung_cap, $trang_thai, $ma_sach]);

        // Xóa thể loại cũ
        $delete_stmt = $pdo->prepare("DELETE FROM sach_the_loai WHERE Ma_sach = ?");
        $delete_stmt->execute([$ma_sach]);

        // Thêm thể loại mới
        if (!empty($the_loai)) {
            $insert_stmt = $pdo->prepare("INSERT INTO sach_the_loai (Ma_sach, Ma_the_loai) VALUES (?, ?)");
            foreach ($the_loai as $ma_the_loai) {
                $insert_stmt->execute([$ma_sach, $ma_the_loai]);
            }
        }

        $pdo->commit();
        header('Location: list_book.php?success=Cập nhật sách thành công');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Lỗi: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM SACH WHERE Ma_sach = ?");
$stmt->execute([$ma_sach]);
$sach = $stmt->fetch();

if (!$sach) {
    die("Không tìm thấy sách.");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật Sách - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .update-book {
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
        
        .form-container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text);
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e6eef8;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 109, 177, 0.1);
        }
        
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }
        
        .book-id {
            background: #f1f5f9;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e6eef8;
        }
        
        .quick-links {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .quick-link {
            background: var(--card-bg);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            color: var(--primary);
            border: 1px solid #e6eef8;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .quick-link:hover {
            background: #eef6ff;
            text-decoration: none;
        }
        
        .status-badge {
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
        
        .status-discontinued {
            background: #e5e7eb;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }
        
        .message.error {
            background: #fee;
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="update-book">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title"> Cập nhật Sách</h1>
                    <p class="sub" style="color: var(--muted); margin-top: 4px;">
                        Chỉnh sửa thông tin sách trong hệ thống thư viện
                    </p>
                </div>
                <div>
                    <a href="list_book.php" class="btn btn-secondary">← Danh sách sách</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="quick-links">
                <a href="../admin/dashboard.php" class="quick-link"> Dashboard</a>
                <a href="list_book.php" class="quick-link"> Danh sách sách</a>
                <a href="add_book.php" class="quick-link"> Thêm sách mới</a>
            </div>

            <!-- Thông báo lỗi -->
            <?php if (isset($error)): ?>
                <div class="message error"> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Form cập nhật -->
            <div class="form-container">
                <form method="POST">
                    <div class="form-grid">
                        <!-- Cột 1 -->
                        <div class="form-group">
                            <label for="ma_sach">Mã sách</label>
                            <div class="book-id"><?= htmlspecialchars($sach['Ma_sach']) ?></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="so_ban">Số bản</label>
                            <input type="number" id="so_ban" name="so_ban" value="<?= htmlspecialchars($sach['So_ban']) ?>" required min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="ten_sach">Tên sách *</label>
                            <input type="text" id="ten_sach" name="ten_sach" value="<?= htmlspecialchars($sach['Ten_sach']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="gia_tien">Giá tiền (VNĐ) *</label>
                            <input type="number" id="gia_tien" name="gia_tien" value="<?= htmlspecialchars($sach['Gia_tien']) ?>" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="ten_tac_gia">Tên tác giả *</label>
                            <input type="text" id="ten_tac_gia" name="ten_tac_gia" value="<?= htmlspecialchars($sach['Ten_tac_gia']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nguon_cung_cap">Nguồn cung cấp *</label>
                            <select id="nguon_cung_cap" name="nguon_cung_cap" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= htmlspecialchars($supplier['Ten_nha_cung_cap']) ?>" 
                                        <?= $sach['Nguon_cung_cap'] == $supplier['Ten_nha_cung_cap'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($supplier['Ten_nha_cung_cap']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="form-group">
                            <label for="nam_xuat_ban">Năm xuất bản</label>
                            <input type="number" id="nam_xuat_ban" name="nam_xuat_ban" value="<?= htmlspecialchars($sach['Nam_xuat_ban']) ?>" min="1900" max="<?= date('Y') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="trang_thai">Trạng thái</label>
                            <select id="trang_thai" name="trang_thai">
                                <option value="Còn" <?= $sach['Trang_thai'] === 'Còn' ? 'selected' : '' ?>>🟢 Còn</option>
                                <option value="Hết" <?= $sach['Trang_thai'] === 'Hết' ? 'selected' : '' ?>>🔴 Hết</option>
                                <option value="Ngưng sử dụng" <?= $sach['Trang_thai'] === 'Ngưng sử dụng' ? 'selected' : '' ?>>⚫ Ngưng sử dụng</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="nha_xuat_ban">Nhà xuất bản</label>
                            <input type="text" id="nha_xuat_ban" name="nha_xuat_ban" value="<?= htmlspecialchars($sach['Nha_xuat_ban']) ?>">
                        </div>
                    </div>

                    <!-- Thể loại (full width) -->
                    <div class="form-group full-width">
                        <label>Thể loại</label>
                        <div class="checkbox-group">
                            <?php foreach ($categories as $category): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="the_loai_<?= $category['Ma_the_loai'] ?>" 
                                           name="the_loai[]" value="<?= $category['Ma_the_loai'] ?>" 
                                           <?= in_array($category['Ma_the_loai'], $current_categories) ? 'checked' : '' ?>>
                                    <label for="the_loai_<?= $category['Ma_the_loai'] ?>" style="font-weight: normal; margin: 0;">
                                        <?= htmlspecialchars($category['Ten_the_loai']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top: 8px;">
                            <a href="add_type.php" class="quick-link" style="font-size: 0.9rem;"> Thêm thể loại mới</a>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"> Cập nhật sách</button>
                        <a href="list_book.php" class="btn btn-secondary"> Hủy bỏ</a>
            
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 