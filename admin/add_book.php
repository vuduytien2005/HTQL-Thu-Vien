<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền thêm sách.");
}

// Lấy danh sách nhà cung cấp từ bảng nha_cung_cap (giống update_book)
$suppliers = $pdo->query("SELECT * FROM nha_cung_cap")->fetchAll();

// Lấy danh sách thể loại từ bảng the_loai (giống update_book)
$categories = $pdo->query("SELECT * FROM the_loai")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_sach = $_POST['ma_sach'];
    $ten_sach = $_POST['ten_sach'];
    $ten_tac_gia = $_POST['ten_tac_gia'];
    $nam_xuat_ban = !empty($_POST['nam_xuat_ban']) ? $_POST['nam_xuat_ban'] : null;
    $nha_xuat_ban = !empty($_POST['nha_xuat_ban']) ? $_POST['nha_xuat_ban'] : null;
    $gia_tien = !empty($_POST['gia_tien']) ? $_POST['gia_tien'] : null;
    $so_ban = $_POST['so_ban'];
    $so_ban_dang_muon = 0; // Mặc định khi thêm sách mới là 0
    $nguon_cung_cap = !empty($_POST['nguon_cung_cap']) ? $_POST['nguon_cung_cap'] : null;
    $trang_thai = $_POST['trang_thai'];
    $ten_the_loai = !empty($_POST['ten_the_loai']) ? $_POST['ten_the_loai'] : null;
    $noi_dung = !empty($_POST['noi_dung']) ? $_POST['noi_dung'] : null;

    try {
        // Thêm sách vào bảng SACH với tất cả các trường
        $stmt = $pdo->prepare("INSERT INTO SACH 
            (Ma_sach, Noi_dung, Ten_sach, Nam_xuat_ban, Nha_xuat_ban, Gia_tien, So_ban, 
             So_ban_dang_muon, Trang_thai, Nguon_cung_cap, Ten_tac_gia, Ten_the_loai)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $ma_sach, 
            $noi_dung, 
            $ten_sach, 
            $nam_xuat_ban, 
            $nha_xuat_ban, 
            $gia_tien, 
            $so_ban, 
            $so_ban_dang_muon, 
            $trang_thai, 
            $nguon_cung_cap, 
            $ten_tac_gia, 
            $ten_the_loai
        ]);

        header('Location: dashboard.php?success=Thêm sách thành công');
        exit();
    } catch (PDOException $e) {
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
            max-width: 1000px;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e6eef8;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 109, 177, 0.1);
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
        
        .warning-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            padding: 12px 16px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        .info-box strong {
            color: var(--primary);
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
                    <a href="dashboard.php" class="btn btn-secondary">← Trang chủ</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="quick-links">
                <a href="list_book.php" class="quick-link">Danh sách sách</a>
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
                    <div class="warning-note">
                        <strong>⚠ Lưu ý:</strong> Các trường có dấu * là bắt buộc.
                    </div>
                    
                    <div class="form-grid">
                        <!-- Cột 1 -->
                        <div class="form-group">
                            <label for="ma_sach" class="required-field">Mã sách</label>
                            <input type="text" id="ma_sach" name="ma_sach" placeholder="VD: SACH001" required>
                            <div class="help-text">Mã sách duy nhất, không được trùng lặp</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="ten_sach" class="required-field">Tên sách</label>
                            <input type="text" id="ten_sach" name="ten_sach" placeholder="Nhập tên sách" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="ten_tac_gia" class="required-field">Tên tác giả</label>
                            <input type="text" id="ten_tac_gia" name="ten_tac_gia" placeholder="Nhập tên tác giả" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nam_xuat_ban">Năm xuất bản</label>
                            <input type="number" id="nam_xuat_ban" name="nam_xuat_ban" 
                                   placeholder="<?= date('Y') ?>" 
                                   min="1900" max="<?= date('Y') ?>">
                            <div class="help-text">Năm xuất bản của sách</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nha_xuat_ban">Nhà xuất bản</label>
                            <input type="text" id="nha_xuat_ban" name="nha_xuat_ban" placeholder="Nhập tên nhà xuất bản">
                            <div class="help-text">Để trống nếu không có</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="so_ban" class="required-field">Số bản hiện có</label>
                            <input type="number" id="so_ban" name="so_ban" placeholder="0" required min="0" value="1">
                            <div class="help-text">Số lượng bản sách có trong kho</div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="form-group">
                            <label for="gia_tien" class="required-field">Giá tiền (VNĐ)</label>
                            <input type="number" id="gia_tien" name="gia_tien" placeholder="0" step="0.01" min="0" required>
                            <div class="help-text">Giá tiền của một bản sách</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="trang_thai" class="required-field">Trạng thái</label>
                            <select id="trang_thai" name="trang_thai" required>
                                <option value="Còn" selected>🟢 Còn (có sẵn)</option>
                                <option value="Hết">🔴 Hết (đã hết sách)</option>
                                <option value="Ngưng sử dụng">⚫ Ngưng sử dụng</option>
                            </select>
                            <div class="help-text">Trạng thái hiện tại của sách</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nguon_cung_cap">Nguồn cung cấp</label>
                            <select id="nguon_cung_cap" name="nguon_cung_cap">
                                <option value="">-- Chọn nhà cung cấp --</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= htmlspecialchars($supplier['Ten_nha_cung_cap']) ?>">
                                        <?= htmlspecialchars($supplier['Ten_nha_cung_cap']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="help-text">Để trống nếu không có</div>
                            <div class="info-box">
                                <strong>Ghi chú:</strong> Muốn thêm nhà cung cấp mới, hãy <a href="add_supplier.php">tại đây</a>.
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="ten_the_loai">Thể loại</label>
                            <select id="ten_the_loai" name="ten_the_loai">
                                <option value="">-- Chọn thể loại --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['Ten_the_loai']) ?>">
                                        <?= htmlspecialchars($category['Ten_the_loai']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="info-box">
                                <strong>Ghi chú:</strong> Sách chỉ có một thể loại chính. Muốn thêm thể loại mới, hãy <a href="add_type.php">tại đây</a>.
                            </div>
                        </div>
                    </div>

                    <!-- Nội dung (full width) -->
                    <div class="form-group full-width">
                        <label for="noi_dung">Nội dung tóm tắt</label>
                        <textarea id="noi_dung" name="noi_dung" placeholder="Nhập nội dung tóm tắt về sách..."></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Thêm sách</button>
                        <a href="dashboard.php" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="reset" class="btn" style="background: var(--muted); color: white;">Làm mới</button>
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