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
    $the_loai = $_POST['the_loai'] ?? []; // Lấy mảng thể loại

    try {
        // Bắt đầu transaction
        $pdo->beginTransaction();

        // Thêm sách vào bảng SACH (KHÔNG có Ten_tac_gia nếu chưa thêm cột này)
        $stmt = $pdo->prepare("INSERT INTO SACH (Ma_sach, Ten_sach, Nam_xuat_ban, Nha_xuat_ban, Gia_tien, So_ban, So_ban_dang_muon, Trang_thai, Nguon_cung_cap)
                               VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)");
        $stmt->execute([$ma_sach, $ten_sach, $nam_xuat_ban, $nha_xuat_ban, $gia_tien, $so_ban, $trang_thai, $nguon_cung_cap]);

        // Thêm thể loại vào bảng trung gian sach_the_loai
        if (!empty($the_loai)) {
            $stmt_the_loai = $pdo->prepare("INSERT INTO sach_the_loai (Ma_sach, Ma_the_loai) VALUES (?, ?)");
            foreach ($the_loai as $ma_the_loai) {
                $stmt_the_loai->execute([$ma_sach, $ma_the_loai]);
            }
        }

        $pdo->commit();
        // CHUYỂN HƯỚNG VỀ DASHBOARD THAY VÌ LIST_BOOK
        header('Location: dashboard.php?success=Thêm sách thành công');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<h3>Thêm sách mới</h3>

<!-- Thông báo lỗi -->
<?php if (isset($error)): ?>
    <p style="color: red;">
        <?php 
        if (strpos($error, 'Duplicate entry') !== false) {
            echo "Mã sách đã tồn tại. Xin vui lòng nhập mã khác.";
        } else {
            echo $error;
        }
        ?>
    </p>
<?php endif; ?>

<!-- Nút quay về Dashboard -->
<p>
    <a href="dashboard.php">← Quay về Dashboard</a>
</p>

<form method="POST">
    <p>
        <label>Mã sách:</label><br>
        <input type="text" name="ma_sach" placeholder="Mã sách" required>
    </p>
    <p>
        <label>Tên sách:</label><br>
        <input type="text" name="ten_sach" placeholder="Tên sách" required>
    </p>
    <p>
        <label>Tên tác giả:</label><br>
        <input type="text" name="ten_tac_gia" placeholder="Tên tác giả">
    </p>
    <p>
        <label>Năm xuất bản:</label><br>
        <input type="number" name="nam_xuat_ban" placeholder="Năm xuất bản">
    </p>
    <p>
        <label>Nhà xuất bản:</label><br>
        <input type="text" name="nha_xuat_ban" placeholder="Nhà xuất bản">
    </p>
    <p>
        <label>Giá tiền:</label><br>
        <input type="number" step="0.01" min="0" name="gia_tien" placeholder="Giá tiền" required>
    </p>
    <p>
        <label>Số bản:</label><br>
        <input type="number" step="0" min="0" name="so_ban" placeholder="Số bản" required>
    </p>
   <p>
    Thể loại:<br>
    <?php foreach ($categories as $category): ?>
        <input type="checkbox" name="the_loai[]" value="<?= $category['Ma_the_loai'] ?>">
        <?= $category['Ten_the_loai'] ?><br>
    <?php endforeach; ?>
    <a href="add_type.php">Thêm thể loại mới</a>
</p>
    <p>
        <label>Nguồn cung cấp:</label><br>
        <select name="nguon_cung_cap" required>
            <option value="">-- Chọn nhà cung cấp --</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= $supplier['Ten_nha_cung_cap'] ?>">
                    <?= htmlspecialchars($supplier['Ten_nha_cung_cap']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <a href="add_supplier.php">Thêm nhà cung cấp mới</a>
    </p>
    <p>
        <label>Trạng thái:</label><br>
        <select name="trang_thai">
            <option value="Còn">Còn</option>
            <option value="Hết">Hết</option>
            <option value="Ngưng sử dụng">Ngưng sử dụng</option>
        </select>
    </p>
    <p>
        <button type="submit">Thêm sách</button>
        <a href="dashboard.php">Hủy</a>
    </p>
</form>