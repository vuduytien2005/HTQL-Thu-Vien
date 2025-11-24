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

<h3>Cập nhật thông tin sách</h3>

<?php if (isset($error)): ?>
    <p><?php echo $error; ?></p>
<?php endif; ?>

<p>
    <a href="/QLY_THUVIEN/HTQL-Thu-Vien/admin/dashboard.php">← Quay về Dashboard</a>
    <a href="list_book.php">← Danh sách sách</a>
</p>

<form method="POST">
    <p>
        <label>Mã sách:</label><br>
        <strong><?= $sach['Ma_sach'] ?></strong>
    </p>
    
    <p>
        <label>Tên sách:</label><br>
        <input type="text" name="ten_sach" value="<?= $sach['Ten_sach'] ?>" required>
    </p>
    
    <p>
        <label>Tên tác giả:</label><br>
        <input type="text" name="ten_tac_gia" value="<?= $sach['Ten_tac_gia'] ?>" required>
    </p>
    
    <p>
        <label>Năm xuất bản:</label><br>
        <input type="number" name="nam_xuat_ban" value="<?= $sach['Nam_xuat_ban'] ?>">
    </p>
    
    <p>
        <label>Nhà xuất bản:</label><br>
        <input type="text" name="nha_xuat_ban" value="<?= $sach['Nha_xuat_ban'] ?>">
    </p>
    
    <p>
        <label>Giá tiền:</label><br>
        <input type="number" step="0.01" min="0" name="gia_tien" value="<?= $sach['Gia_tien'] ?>" required>
    </p>
    
    <p>
        <label>Số bản:</label><br>
        <input type="number" name="so_ban" value="<?= $sach['So_ban'] ?>" required>
    </p>
    
    <p>
        <label>Thể loại:</label><br>
        <?php foreach ($categories as $category): ?>
            <input type="checkbox" name="the_loai[]" value="<?= $category['Ma_the_loai'] ?>" 
                <?= in_array($category['Ma_the_loai'], $current_categories) ? 'checked' : '' ?>>
            <?= $category['Ten_the_loai'] ?><br>
        <?php endforeach; ?>
        <a href="add_type.php">Thêm thể loại mới</a>
    </p>
    
    <p>
        <label>Nguồn cung cấp:</label><br>
        <select name="nguon_cung_cap" required>
            <option value="">-- Chọn nhà cung cấp --</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= $supplier['Ten_nha_cung_cap'] ?>" 
                    <?= $sach['Nguon_cung_cap'] == $supplier['Ten_nha_cung_cap'] ? 'selected' : '' ?>>
                    <?= $supplier['Ten_nha_cung_cap'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    
    <p>
        <label>Trạng thái:</label><br>
        <select name="trang_thai">
            <option value="Còn" <?= $sach['Trang_thai'] === 'Còn' ? 'selected' : '' ?>>Còn</option>
            <option value="Hết" <?= $sach['Trang_thai'] === 'Hết' ? 'selected' : '' ?>>Hết</option>
            <option value="Ngưng sử dụng" <?= $sach['Trang_thai'] === 'Ngưng sử dụng' ? 'selected' : '' ?>>Ngưng sử dụng</option>
        </select>
    </p>
    
    <p>
        <button type="submit">Cập nhật sách</button>
        <a href="list_book.php">Hủy</a>
    </p>
</form>