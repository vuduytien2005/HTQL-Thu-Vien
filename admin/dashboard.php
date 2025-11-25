<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

// Lấy tên quản trị viên đang đăng nhập
$current_admin = $_SESSION['user']['username'] ?? 'Quản trị viên';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị Thư viện</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .card { border: 1px solid #ccc; border-radius: 8px; padding: 15px; text-align: center; flex: 1; }
        .card p { margin: 0; font-weight: bold; }
        .card h3 { margin: 5px 0 0; color: #007bff; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div>
        <div>
            <h2>Thư viện Admin</h2>
            <p>Quản trị hệ thống</p>
        </div>
        <div>
            <ul>
                <li><a href="list_book.php">Quản lý Sách</a></li>
                <li><a href="create_account.php">Tạo tài khoản</a></li>
                <li><a href="list_account.php">Quản lý Tài khoản</a></li>
                <li><a href="create_report.php">Tạo báo cáo</a></li>
                <li><a href="list_report.php">Báo cáo & Thống kê</a></li>
                <li><a href="../auth/logout.php">Đăng xuất</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div>
        <div>
            <h1>Dashboard Quản trị</h1>
            
            <!-- Thông báo thành công -->
            <?php if (isset($_GET['success'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 5px;">
                    ✅ <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <div>
                <h4>Xin chào, <?= htmlspecialchars($current_admin) ?></h4>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats">
            <div class="card">
                <p>Tổng số quản trị viên</p>
                <h3 id="total-admins">0</h3>
            </div>
            <div class="card">
                <p>Tổng số sách</p>
                <h3 id="total-books">0</h3>
            </div>
            <div class="card">
                <p>Độc giả</p>
                <h3 id="total-readers">0</h3>
            </div>
            <div class="card">
                <p>Lượt mượn sách</p>
                <h3 id="total-borrows">0</h3>
            </div>
            <div class="card">
                <p>Tiền phạt (VNĐ)</p>
                <h3 id="total-fines">0</h3>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <h2>Thao tác nhanh</h2>
            <a href="add_book.php">Thêm sách mới</a> |
            <a href="create_account.php">Tạo tài khoản</a> |
            <a href="create_report.php">Tạo báo cáo</a>
        </div>

        <!-- Recent Books -->
        <div>
            <h2>Sách mới thêm gần đây</h2>
            <a href="list_book.php">Xem tất cả sách</a>
            <div id="recent-books">Đang tải dữ liệu sách...</div>
        </div>

        <!-- Recent Reports -->
        <div>
            <h2>Báo cáo gần đây</h2>
            <a href="list_report.php">Xem tất cả báo cáo</a>
            <div id="recent-reports">Đang tải dữ liệu báo cáo...</div>
        </div>
    </div>

    <script>
        function formatNumber(num) {
            return new Intl.NumberFormat('vi-VN').format(num);
        }
        function formatCurrency(amount) {
            return formatNumber(amount) + ' VNĐ';
        }
        async function loadStats() {
            try {
                const response = await fetch('get_dashboard_stats.php');
                const data = await response.json();
                if (data.success) {
                    document.getElementById('total-admins').textContent = formatNumber(data.total_admins);
                    document.getElementById('total-books').textContent = formatNumber(data.total_books);
                    document.getElementById('total-readers').textContent = formatNumber(data.total_readers);
                    document.getElementById('total-borrows').textContent = formatNumber(data.total_borrows);
                    document.getElementById('total-fines').textContent = formatCurrency(data.total_fines);
                }
            } catch (error) {
                console.error('Lỗi tải thống kê:', error);
            }
        }
        async function loadRecentBooks() {
            try {
                const response = await fetch('get_recent_books.php');
                const data = await response.json();
                const container = document.getElementById('recent-books');
                if (data.success && data.books.length > 0) {
                    let html = `<table border="1"><thead><tr>
                        <th>Mã sách</th><th>Tên sách</th><th>Nhà xuất bản</th><th>Giá tiền</th><th>Trạng thái</th><th>Thao tác</th>
                        </tr></thead><tbody>`;
                    data.books.forEach(book => {
                        html += `<tr>
                            <td>${book.Ma_sach}</td>
                            <td>${book.Ten_sach}</td>
                            <td>${book.Nha_xuat_ban || 'N/A'}</td>
                            <td>${formatCurrency(book.Gia_tien || 0)}</td>
                            <td>${book.Trang_thai}</td>
                            <td>
                                <a href="update_book.php?ma_sach=${book.Ma_sach}">Sửa</a>
                                <a href="delete_book.php?ma_sach=${book.Ma_sach}" onclick="return confirm('Bạn có chắc muốn xóa sách này?')">Xóa</a>
                            </td>
                        </tr>`;
                    });
                    html += `</tbody></table>`;
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p>Chưa có sách nào trong hệ thống</p>';
                }
            } catch (error) {
                console.error('Lỗi tải sách:', error);
                document.getElementById('recent-books').innerHTML = '<p>Lỗi tải dữ liệu sách</p>';
            }
        }
        async function loadRecentReports() {
            try {
                const response = await fetch('get_recent_reports.php');
                const data = await response.json();
                const container = document.getElementById('recent-reports');
                if (data.success && data.reports.length > 0) {
                    let html = `<table border="1"><thead><tr>
                        <th>ID</th><th>Loại báo cáo</th><th>Thời gian</th><th>Người tạo</th><th>Thao tác</th>
                        </tr></thead><tbody>`;
                    data.reports.forEach(report => {
                        html += `<tr>
                            <td>${report.Ma_bao_cao}</td>
                            <td>${report.Loai_bao_cao}</td>
                            <td>${new Date(report.Thoi_gian_tao).toLocaleString('vi-VN')}</td>
                            <td>${report.Nguoi_tao}</td>
                            <td><a href="view_report.php?id=${report.Ma_bao_cao}">Xem chi tiết</a></td>
                        </tr>`;
                    });
                    html += `</tbody></table>`;
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p>Chưa có báo cáo nào</p>';
                }
            } catch (error) {
                console.error('Lỗi tải báo cáo:', error);
                document.getElementById('recent-reports').innerHTML = '<p>Lỗi tải dữ liệu báo cáo</p>';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadRecentBooks();
            loadRecentReports();
        });
    </script>
</body>
</html>