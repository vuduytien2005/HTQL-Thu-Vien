<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

// Xử lý cập nhật tài khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE TAI_KHOAN SET username = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $role, $hashed_password, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE TAI_KHOAN SET username = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $role, $id]);
    }
    
    header('Location: list_account.php?success=Cập nhật tài khoản thành công');
    exit();
}

// Xử lý hiển thị form sửa
$edit_account = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT id, username, role FROM TAI_KHOAN WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $edit_account = $stmt->fetch();
}

$stmt = $pdo->query("SELECT id, username, role FROM TAI_KHOAN");
$accounts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tài khoản - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .account-management {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }
        
        .btn-edit {
            background: var(--primary);
            color: white;
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .btn-edit:hover {
            background: #0d5a9d;
            text-decoration: none;
        }
        
        .btn-delete:hover {
            background: #c81e4a;
            text-decoration: none;
        }
        
        .edit-form-container {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 24px;
            border-left: 4px solid var(--primary);
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text);
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-admin {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        
        .role-user {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .role-docgia {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        .table-container {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .page-header {
            display: flex;
            justify-content: between;
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
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-sm {
                text-align: center;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 600px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="account-management">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">📊 Quản lý Tài khoản</h1>
                    <p class="sub" style="color: var(--muted); margin-top: 4px;">Quản lý tất cả tài khoản trong hệ thống</p>
                </div>
                <a href="../admin/dashboard.php" class="btn btn-secondary">← Quay về Dashboard</a>
            </div>

            <!-- Thông báo -->
            <?php if (isset($_GET['success'])): ?>
                <div class="message success">✅ <?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>

            <!-- Form sửa tài khoản -->
            <?php if ($edit_account): ?>
                <div class="edit-form-container">
                    <h3 style="margin-bottom: 20px; color: var(--primary);">✏️ Sửa tài khoản: <?= htmlspecialchars($edit_account['username']) ?></h3>
                    <form method="post" action="list_account.php">
                        <input type="hidden" name="id" value="<?= $edit_account['id'] ?>">
                        <input type="hidden" name="update_account" value="1">
                        
                        <div class="form-group">
                            <label for="username">Tên đăng nhập:</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($edit_account['username']) ?>" required 
                                   style="width: 100%; max-width: 400px;">
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Vai trò:</label>
                            <select id="role" name="role" style="width: 100%; max-width: 400px; padding: 10px;">
                                <option value="docgia" <?= $edit_account['role'] == 'docgia' ? 'selected' : '' ?>>Độc giả</option>
                                <option value="admin" <?= $edit_account['role'] == 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mật khẩu mới (để trống nếu không đổi):</label>
                            <input type="password" id="password" name="password" 
                                   style="width: 100%; max-width: 400px;" 
                                   placeholder="Nhập mật khẩu mới...">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Cập nhật tài khoản</button>
                            <a href="list_account.php" class="btn btn-secondary">❌ Hủy bỏ</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Bảng danh sách tài khoản -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Vai trò</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $acc): ?>
                            <tr>
                                <td><strong>#<?= $acc['id'] ?></strong></td>
                                <td><?= htmlspecialchars($acc['username']) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $acc['role'] ?>">
                                        <?= $acc['role'] === 'admin' ? '👑 Admin' : ($acc['role'] === 'docgia' ? ' Độc giả' : '👤 User') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="list_account.php?edit_id=<?= $acc['id'] ?>" class="btn-sm btn-edit"> Sửa</a>
                                        <a href="delete_account.php?id=<?= $acc['id'] ?>" 
                                           class="btn-sm btn-delete"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản <?= htmlspecialchars($acc['username']) ?>?')">
                                            Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Thống kê nhanh -->
            <div style="margin-top: 20px; padding: 16px; background: var(--card-bg); border-radius: 8px;">
                <p style="margin: 0; color: var(--muted); font-size: 0.9rem;">
                     Tổng số tài khoản: <strong><?= count($accounts) ?></strong> | 
                    Quản trị viên: <strong><?= count(array_filter($accounts, fn($acc) => $acc['role'] === 'admin')) ?></strong> | 
                    Độc giả: <strong><?= count(array_filter($accounts, fn($acc) => $acc['role'] === 'docgia')) ?></strong>
                </p>
            </div>
        </div>
    </div>
</body>
</html>