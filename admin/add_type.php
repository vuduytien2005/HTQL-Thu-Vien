<?php
session_start();
require '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    die("Bạn không có quyền truy cập.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_the_loai = trim($_POST['ten_the_loai'] ?? '');
    
    if (empty($ten_the_loai)) {
        $error = 'Vui lòng nhập tên thể loại!';
    } else {
        try {
            // Kiểm tra thể loại đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT * FROM the_loai WHERE Ten_the_loai = ?");
            $stmt->execute([$ten_the_loai]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Thể loại đã tồn tại! Vui lòng chọn tên khác.';
            } else {
                // Tạo mã thể loại tự động
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM the_loai");
                $result = $stmt->fetch();
                $ma_the_loai = 'TL' . str_pad($result['count'] + 1, 3, '0', STR_PAD_LEFT);
                
                // Thêm thể loại mới
                $stmt = $pdo->prepare("INSERT INTO the_loai (Ma_the_loai, Ten_the_loai) VALUES (?, ?)");
                $stmt->execute([$ma_the_loai, $ten_the_loai]);
                
                header('Location: add_book.php?success=Thêm thể loại thành công');
                exit();
            }
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm thể loại</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .card-box {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }
        
        h3 {
            margin-bottom: 20px;
            color: var(--primary);
            font-size: 1.3rem;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text);
        }
        
        input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e6eef8;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #fff;
            transition: all 0.3s ease;
            margin-bottom: 16px;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 109, 177, 0.1);
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .back-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .message {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.error {
            background: #fee;
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        .message.success {
            background: #efe;
            border: 1px solid var(--success);
            color: var(--success);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card-box">
            <h3>Thêm thể loại mới</h3>
            
            <?php if (isset($error)): ?>
                <div class="message error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="message success">
                    ✅ <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <label for="ten_the_loai">Tên thể loại</label>
                <input id="ten_the_loai" type="text" name="ten_the_loai" 
                       value="<?php echo isset($_POST['ten_the_loai']) ? htmlspecialchars($_POST['ten_the_loai']) : ''; ?>" 
                       required 
                       placeholder="Nhập tên thể loại...">
                
                <div style="margin-top:16px; display: flex; gap: 12px; align-items: center;">
                    <button class="btn btn-primary" type="submit">Thêm thể loại</button>
                    <a class="back-link" href="add_book.php">Quay lại thêm sách</a>
                </div>
            </form>
        </div>
        
        <div style="margin-top:16px;">
            <a class="back-link" href="dashboard.php">← Quay về Dashboard</a>
        </div>
    </div>
</body>
</html>