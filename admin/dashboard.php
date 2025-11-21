<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị Thư viện</title>
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
                <li><a href="#" >Dashboard</a></li>
                <li><a href="list_books.php">Quản lý Sách</a></li>
                <li><a href="create_account.php">Tạo tài khoản</a></li>
                <li><a href="list_account.php">Quản lý Tài khoản</a></li>
                <li><a href="create_report.php">Tạo báo cáo</a></li>
                <li><a href="list_report.php">Báo cáo & Thống kê</a></li>
                <li><a href="../logout.php">Đăng xuất</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div>
        <div>
            <h1>Dashboard Quản trị</h1>
            <div>
                <div>
                    <h4>Quản trị viên</h4>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div>
            <div>
                <div>
                    <h3 id="total-books">0</h3>
                    <p>Tổng số sách</p>
                </div>
            </div>
            <div>
                <div>
                    <h3 id="total-readers">0</h3>
                    <p>Độc giả</p>
                </div>
            </div>
            <div>
                <div>
                    <h3 id="total-borrows">0</h3>
                    <p>Lượt mượn sách</p>
                </div>
            </div>
            <div>
                <div>
                    <h3 id="total-fines">0</h3>
                    <p>Tiền phạt (VNĐ)</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <div>
                <h2>Thao tác nhanh</h2>
            </div>
            <div>
                <a href="add_book.php">Thêm sách mới</a>
                <a href="create_account.php">Tạo tài khoản</a>
                <a href="create_report.php">Tạo báo cáo</a>
            </div>
        </div>

        <!-- Recent Books -->
        <div>
            <div>
                <h2>Sách mới thêm gần đây</h2>
                <a href="list_books.php">Xem tất cả sách</a>
            </div>
            <div id="recent-books">
                <div>Đang tải dữ liệu sách...</div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div>
            <div>
                <h2>Báo cáo gần đây</h2>
                <a href="list_report.php">Xem tất cả báo cáo</a>
            </div>
            <div id="recent-reports">
                <div>Đang tải dữ liệu báo cáo...</div>
            </div>
        </div>
    </div>

    <script>
        // Hàm định dạng số
        function formatNumber(num) {
            return new Intl.NumberFormat('vi-VN').format(num);
        }

        // Hàm định dạng tiền
        function formatCurrency(amount) {
            return formatNumber(amount) + ' VNĐ';
        }

        // Hàm tải thống kê
        async function loadStats() {
            try {
                const response = await fetch('get_dashboard_stats.php');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('total-books').textContent = formatNumber(data.total_books);
                    document.getElementById('total-readers').textContent = formatNumber(data.total_readers);
                    document.getElementById('total-borrows').textContent = formatNumber(data.total_borrows);
                    document.getElementById('total-fines').textContent = formatCurrency(data.total_fines);
                }
            } catch (error) {
                console.error('Lỗi tải thống kê:', error);
            }
        }

        // Hàm tải sách gần đây
        async function loadRecentBooks() {
            try {
                const response = await fetch('get_recent_books.php');
                const data = await response.json();
                
                const container = document.getElementById('recent-books');
                
                if (data.success && data.books.length > 0) {
                    let html = `
                        <table border="1">
                            <thead>
                                <tr>
                                    <th>Mã sách</th>
                                    <th>Tên sách</th>
                                    <th>Nhà xuất bản</th>
                                    <th>Giá tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    data.books.forEach(book => {
                        html += `
                            <tr>
                                <td>${book.Ma_sach}</td>
                                <td>${book.Ten_sach}</td>
                                <td>${book.Nha_xuat_ban || 'N/A'}</td>
                                <td>${formatCurrency(book.Gia_tien || 0)}</td>
                                <td>${book.Trang_thai}</td>
                                <td>
                                    <a href="update_book.php?ma_sach=${book.Ma_sach}">Sửa</a>
                                    <a href="delete_book.php?ma_sach=${book.Ma_sach}">Xóa</a>
                                </td>
                            </tr>
                        `;
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

        // Hàm tải báo cáo gần đây
        async function loadRecentReports() {
            try {
                const response = await fetch('get_recent_reports.php');
                const data = await response.json();
                
                const container = document.getElementById('recent-reports');
                
                if (data.success && data.reports.length > 0) {
                    let html = `
                        <table border="1">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Loại báo cáo</th>
                                    <th>Thời gian</th>
                                    <th>Người tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    data.reports.forEach(report => {
                        html += `
                            <tr>
                                <td>${report.Ma_bao_cao}</td>
                                <td>${report.Loai_bao_cao}</td>
                                <td>${new Date(report.Thoi_gian_tao).toLocaleString('vi-VN')}</td>
                                <td>${report.Nguoi_tao}</td>
                                <td><a href="view_report.php?id=${report.Ma_bao_cao}">Xem chi tiết</a></td>
                            </tr>
                        `;
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

        // Tải tất cả dữ liệu khi trang được load
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadRecentBooks();
            loadRecentReports();
        });
    </script>
</body>
</html>