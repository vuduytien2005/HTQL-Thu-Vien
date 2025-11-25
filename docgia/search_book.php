<?php
session_start();
require '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Xử lý tìm kiếm
$keyword = $_GET['keyword'] ?? '';
$search_by = $_GET['search_by'] ?? 'Ten_sach'; // Mặc định tìm theo tên sách
$results = [];

// Các cột hợp lệ để tìm kiếm
$valid_columns = ['Ten_sach', 'Ten_tac_gia', 'Ten_the_loai'];

if ($keyword !== '' && in_array($search_by, $valid_columns)) {
    $stmt = $pdo->prepare("SELECT * FROM SACH WHERE $search_by LIKE ?");
    $stmt->execute(["%$keyword%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>🔎 Tìm kiếm sách</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h1>🔎 Tìm kiếm sách</h1>
    <form method="GET" action="">
        <input type="text" name="keyword" placeholder="Nhập từ khóa" value="<?php echo htmlspecialchars($keyword); ?>" required>
        <select name="search_by">
            <option value="Ten_sach" <?php echo ($search_by=='Ten_sach')?'selected':''; ?>>Tên sách</option>
            <option value="Ten_tac_gia" <?php echo ($search_by=='Ten_tac_gia')?'selected':''; ?>>Tên tác giả</option>
            <option value="Ten_the_loai" <?php echo ($search_by=='Ten_the_loai')?'selected':''; ?>>Thể loại</option>
        </select>
        <button type="submit">Tìm kiếm</button>
    </form>

    <br>

    <?php if ($keyword !== ''): ?>
        <h2>Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($keyword); ?>" theo <?php echo $search_by; ?></h2>
        <?php if (count($results) > 0): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th>Mã sách</th>
                    <th>Tên sách</th>
                    <th>Tác giả</th>
                    <th>Thể loại</th>
                    <th>Nhà xuất bản</th>
                    <th>Năm xuất bản</th>
                    <th>Giá tiền</th>
                    <th>Số bản</th>
                    <th>Số bản đang mượn</th>
                    <th>Trạng thái</th>
                    <th>Nguồn cung cấp</th>
                </tr>
                <?php foreach ($results as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['Ma_sach']); ?></td>
                        <td><?php echo htmlspecialchars($book['Ten_sach']); ?></td>
                        <td><?php echo htmlspecialchars($book['Ten_tac_gia']); ?></td>
                        <td><?php echo htmlspecialchars($book['Ten_the_loai']); ?></td>
                        <td><?php echo htmlspecialchars($book['Nha_xuat_ban']); ?></td>
                        <td><?php echo htmlspecialchars($book['Nam_xuat_ban']); ?></td>
                        <td><?php echo htmlspecialchars($book['Gia_tien']); ?></td>
                        <td><?php echo htmlspecialchars($book['So_ban']); ?></td>
                        <td><?php echo htmlspecialchars($book['So_ban_dang_muon']); ?></td>
                        <td><?php echo htmlspecialchars($book['Trang_thai']); ?></td>
                        <td><?php echo htmlspecialchars($book['Nguon_cung_cap']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>❌ Không tìm thấy sách nào.</p>
        <?php endif; ?>
    <?php endif; ?>

    <br>
    <a href="dashboard.php" class="button">⬅ Quay lại dashboard</a>
</div>
</body>
</html>
