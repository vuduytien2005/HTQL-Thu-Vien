<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền thêm sách.");
}

// Lấy danh sách nhà cung cấp từ database
$suppliers = $pdo->query("SELECT * FROM nha_cung_cap")->fetchAll();

// Lấy danh sách thể loại từ database
$categories = $pdo->query("SELECT * FROM the_loai")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_sach = $_POST['ma_sach'];
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

        // Thêm sách vào bảng SACH
        $stmt = $pdo->prepare("INSERT INTO SACH (Ma_sach, Ten_sach, Ten_tac_gia, Nam_xuat_ban, Nha_xuat_ban, Gia_tien, So_ban, So_ban_dang_muon, Trang_thai, Nguon_cung_cap)
                               VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?)");
        $stmt->execute([$ma_sach, $ten_sach, $ten_tac_gia, $nam_xuat_ban, $nha_xuat_ban, $gia_tien, $so_ban, $trang_thai, $nguon_cung_cap]);

        // Thêm thể loại vào bảng trung gian sach_the_loai
        if (!empty($the_loai)) {
            $stmt_the_loai = $pdo->prepare("INSERT INTO sach_the_loai (Ma_sach, Ma_the_loai) VALUES (?, ?)");
            foreach ($the_loai as $ma_the_loai) {
                $stmt_the_loai->execute([$ma_sach, $ma_the_loai]);
            }
        }

        $pdo->commit();
        header('Location: dashboard.php?success=Thêm sách thành công');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Sách Mới - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .add-book {
            max-width: 900px;
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
            transition: all 0.3s ease;
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
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
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
            font-size: 0.9rem;
        }
        
        .quick-link:hover {
            background: #eef6ff;
            text-decoration: none;
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
        
        .message.error {
            background: #fee;
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #efe;
            border: 1px solid var(--success);
            color: var(--success);
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
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
            
            .checkbox-group {
                grid-template-columns: 1fr;
            }
        }
        
        .supplier-select {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .supplier-select select {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="add-book">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">📖 Thêm Sách Mới</h1>
                    <p class="sub" style="color: var(--muted); margin-top: 4px;">
                        Thêm sách mới vào hệ thống thư viện
                    </p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="quick-links">
                <a href="dashboard.php" class="quick-link"> Dashboard</a>
                <a href="list_book.php" class="quick-link"> Danh sách sách</a>
                <a href="add_type.php" class="quick-link"> Thêm thể loại</a>
                <a href="add_supplier.php" class="quick-link"> Thêm nhà cung cấp</a>
            </div>

            <!-- Thông báo lỗi -->
            <?php if (isset($error)): ?>
                <div class="message error">
                    ❌ <?php 
                    if (strpos($error, 'Duplicate entry') !== false) {
                        echo "Mã sách đã tồn tại. Xin vui lòng nhập mã khác.";
                    } else {
                        echo htmlspecialchars($error);
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Thông báo thành công -->
            <?php if (isset($_GET['success'])): ?>
                <div class="message success">✅ <?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>

            <!-- Form thêm sách -->
            <div class="form-container">
                <form method="POST">
                    <div class="form-grid">
                        <!-- Cột 1 -->
                        <div class="form-group">
                            <label for="ma_sach" class="required-field">Mã sách</label>
                            <input type="text" id="ma_sach" name="ma_sach" placeholder="VD: SACH001" required>
                            <div class="help-text">Mã sách duy nhất, không được trùng lặp</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="so_ban" class="required-field">Số bản</label>
                            <input type="number" id="so_ban" name="so_ban" placeholder="0" required min="0" value="1">
                            <div class="help-text">Số lượng bản sách có trong kho</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="ten_sach" class="required-field">Tên sách</label>
                            <input type="text" id="ten_sach" name="ten_sach" placeholder="Nhập tên sách" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="gia_tien" class="required-field">Giá tiền (VNĐ)</label>
                            <input type="number" id="gia_tien" name="gia_tien" placeholder="0" step="0.01" min="0" required>
                            <div class="help-text">Giá tiền của một bản sách</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="ten_tac_gia" class="required-field">Tên tác giả</label>
                            <input type="text" id="ten_tac_gia" name="ten_tac_gia" placeholder="Nhập tên tác giả" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nguon_cung_cap" class="required-field">Nguồn cung cấp</label>
                            <div class="supplier-select">
                                <select id="nguon_cung_cap" name="nguon_cung_cap" required>
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= htmlspecialchars($supplier['Ten_nha_cung_cap']) ?>">
                                            <?= htmlspecialchars($supplier['Ten_nha_cung_cap']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="add_supplier.php" class="quick-link" title="Thêm nhà cung cấp mới">➕</a>
                            </div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="form-group">
                            <label for="nam_xuat_ban">Năm xuất bản</label>
                            <input type="number" id="nam_xuat_ban" name="nam_xuat_ban" 
                                   placeholder="<?= date('Y') ?>" 
                                   min="1900" max="<?= date('Y') ?>">
                            <div class="help-text">Năm xuất bản của sách</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="trang_thai">Trạng thái</label>
                            <select id="trang_thai" name="trang_thai">
                                <option value="Còn">🟢 Còn</option>
                                <option value="Hết">🔴 Hết</option>
                                <option value="Ngưng sử dụng">⚫ Ngưng sử dụng</option>
                            </select>
                            <div class="help-text">Trạng thái hiện tại của sách</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nha_xuat_ban">Nhà xuất bản</label>
                            <input type="text" id="nha_xuat_ban" name="nha_xuat_ban" placeholder="Nhập tên nhà xuất bản">
                        </div>
                    </div>

                    <!-- Thể loại (full width) -->
                    <div class="form-group full-width">
                        <label>Thể loại</label>
                        <div class="checkbox-group">
                            <?php foreach ($categories as $category): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="the_loai_<?= $category['Ma_the_loai'] ?>" 
                                           name="the_loai[]" value="<?= $category['Ma_the_loai'] ?>">
                                    <label for="the_loai_<?= $category['Ma_the_loai'] ?>" style="font-weight: normal; margin: 0;">
                                        <?= htmlspecialchars($category['Ten_the_loai']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top: 8px;">
                            <a href="add_type.php" class="quick-link">➕ Thêm thể loại mới</a>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"> Thêm sách</button>
                        <a href="dashboard.php" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="reset" class="btn" style="background: var(--muted); color: white;"> Làm mới</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tự động tạo mã sách nếu để trống
        document.getElementById('ma_sach').addEventListener('blur', function() {
            if (!this.value.trim()) {
                const timestamp = Date.now().toString().slice(-6);
                this.value = 'SACH' + timestamp;
            }
        });

        // Hiển thị preview giá tiền
        document.getElementById('gia_tien').addEventListener('input', function() {
            const value = parseFloat(this.value) || 0;
            const formatted = new Intl.NumberFormat('vi-VN').format(value);
            this.title = formatted + ' VNĐ';
        });

        // Validate năm xuất bản
        document.getElementById('nam_xuat_ban').addEventListener('input', function() {
            const currentYear = new Date().getFullYear();
            if (this.value > currentYear) {
                this.setCustomValidity('Năm xuất bản không thể lớn hơn năm hiện tại');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>