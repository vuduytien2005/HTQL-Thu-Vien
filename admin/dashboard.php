<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

$current_admin = $_SESSION['user']['username'] ?? 'Quản trị viên';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, rgba(16,109,177,0.95), rgba(14,165,169,0.95));
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .sidebar h2 { margin: 0 0 8px; font-size: 1.3rem; }
        .sidebar p { margin: 0; opacity: 0.9; }
        .sidebar ul { list-style: none; margin-top: 16px; }
        .sidebar ul li { margin-bottom: 8px; }
        .sidebar ul li a { color: #fff; text-decoration: none; font-weight: 600; }
        .sidebar ul li a:hover { text-decoration: underline; }
        
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin: 20px 0; }
        .stat-card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--card-shadow);
        }
        .stat-card p { margin: 0 0 10px; color: var(--muted); font-weight: 600; }
        .stat-card h3 { margin: 0; color: var(--primary); font-size: 1.8rem; }
        
        .quick-actions { margin: 20px 0; }
        .quick-actions a { 
            display: inline-block;
            padding: 10px 16px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            margin-right: 8px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .quick-actions a:hover { opacity: 0.9; }
        
        table { width: 100%; border-collapse: collapse; background: var(--card-bg); border-radius: 6px; overflow: hidden; margin-top: 12px; }
        th, td { padding: 12px; border-bottom: 1px solid #eef2f7; text-align: left; }
        th { background: #fbfdff; color: var(--muted); font-weight: 700; }
        td a { color: var(--primary); margin-right: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h2> Quản trị Thư viện</h2>
            <div class="sub">Chào mừng, <?= htmlspecialchars($current_admin) ?></div>
        </div>

        <!-- Menu -->
        <div class="menu">
            <a href="list_book.php"> Quản lý Sách</a>
            <a href="create_account.php"> Tạo tài khoản</a>
            <a href="list_account.php"> Quản lý Tài khoản</a>
            <a href="create_report.php"> Tạo báo cáo</a>
            <a href="list_report.php"> Báo cáo & Thống kê</a>
            <a href="../auth/logout.php"> Đăng xuất</a>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['success'])): ?>
            <div class="message success">
                ✅ <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats">
            <div class="stat-card">
                <p>Tổng số quản trị viên</p>
                <h3 id="total-admins">0</h3>
            </div>
            <div class="stat-card">
                <p>Tổng số sách</p>
                <h3 id="total-books">0</h3>
            </div>
            <div class="stat-card">
                <p>Độc giả</p>
                <h3 id="total-readers">0</h3>
            </div>
            <div class="stat-card">
                <p>Lượt mượn sách</p>
                <h3 id="total-borrows">0</h3>
            </div>
            <div class="stat-card">
                <p>Tiền phạt (VNĐ)</p>
                <h3 id="total-fines">0</h3>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="add_book.php">Thêm sách mới</a>
            <a href="create_account.php">Tạo tài khoản</a>
            <a href="create_report.php">Tạo báo cáo</a>
        </div>

        <!-- Recent Books -->
        <div class="card-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="margin: 0; color: var(--primary);"> Sách mới thêm gần đây</h3>
                <a href="list_book.php" style="color: var(--primary);">Xem tất cả →</a>
            </div>
            <div id="recent-books">Đang tải dữ liệu sách...</div>
        </div>

        <!-- Recent Reports -->
        <div class="card-box" style="margin-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="margin: 0; color: var(--primary);"> Báo cáo gần đây</h3>
                <a href="list_report.php" style="color: var(--primary);">Xem tất cả →</a>
            </div>
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