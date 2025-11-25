<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

// Xử lý thông báo
if (isset($_GET['delete_success'])) {
    echo "<p>✅ " . $_GET['delete_success'] . "</p>";
}
if (isset($_GET['success'])) {
    echo "<p>✅ " . $_GET['success'] . "</p>";
}

// Lấy danh sách sách kèm thể loại
$stmt = $pdo->query("
    SELECT s.*, 
           GROUP_CONCAT(tl.Ten_the_loai SEPARATOR ', ') as Danh_sach_the_loai
    FROM SACH s
    LEFT JOIN sach_the_loai stl ON s.Ma_sach = stl.Ma_sach
    LEFT JOIN the_loai tl ON stl.Ma_the_loai = tl.Ma_the_loai
    GROUP BY s.Ma_sach
    ORDER BY s.Ma_sach DESC
");
$books = $stmt->fetchAll();
?>

<h3>Danh sách sách</h3>

<table border="1">
    <tr>
        <th>Mã sách</th>
        <th>Tên sách</th>
        <th>Tác giả</th>
        <th>Thể loại</th>
        <th>Nhà XB</th>
        <th>Năm XB</th>
        <th>Giá</th>
        <th>Số bản</th>
        <th>Đang mượn</th>
        <th>Thao tác</th>
    </tr>
    <?php foreach ($books as $book): ?>
    <tr>
        <td><?= $book['Ma_sach'] ?></td>
        <td><?= $book['Ten_sach'] ?></td>
        <td><?= $book['Ten_tac_gia'] ?></td>
        <td><?= $book['Danh_sach_the_loai'] ?? 'Chưa phân loại' ?></td>
        <td><?= $book['Nha_xuat_ban'] ?></td>
        <td><?= $book['Nam_xuat_ban'] ?></td>
        <td><?= number_format($book['Gia_tien'], 0, ',', '.') ?> VNĐ</td>
        <td><?= $book['So_ban'] ?></td>
        <td><?= $book['So_ban_dang_muon'] ?></td>
        <td>
            <a href="update_book.php?ma_sach=<?= $book['Ma_sach'] ?>">Sửa</a>
            <a href="delete_book.php?ma_sach=<?= $book['Ma_sach'] ?>"
             onclick='return confirm("Bạn có chắc chắn muốn xóa sách <?= htmlspecialchars($book['Ten_sach']) ?> không?")'>Xóa</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<p><a href="add_book.php">Thêm sách mới</a></p>

<p>
    <a href="dashboard.php">← Quay về Dashboard</a>
</p>