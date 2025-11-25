<?php
include 'db.php';
$Ma_doc_gia = $_GET['Ma_doc_gia'];
$message = "";

// Lấy thông tin hiện tại
$stmt = $conn->prepare("SELECT * FROM doc_gia WHERE Ma_doc_gia=? AND role='user'");
$stmt->bind_param("i",$Ma_doc_gia);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) die("Người dùng không tồn tại!");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($password != "") {
        $password = password_hash($password,PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE doc_gia SET username=?, email=?, password=? WHERE Ma_doc_gia=? AND role='user'");
        $stmt->bind_param("sssi",$username,$email,$password,$Ma_doc_gia);
    } else {
        $stmt = $conn->prepare("UPDATE doc_gia SET username=?, email=? WHERE Ma_doc_gia=? AND role='user'");
        $stmt->bind_param("ssi",$username,$email,$Ma_doc_gia);
    }
    $stmt->execute();
    $stmt->close();
    $message = "Cập nhật thành công!";
}
?>

<h2>Sửa độc giả</h2>
<form method="POST">
    Username: <input type="text" name="username" placeholder="Username" value="<?php echo $user['username']; ?>" required><br>
    Email: <input type="email" name="email" placeholder="Email" value="<?php echo $user['email']; ?>" required><br>
    Password: <input type="password" name="password" placeholder="Password" placeholder="Để trống nếu không đổi"><br>
    <input type="submit" value="Cập nhật">
</form>
<p><?php echo $message; ?></p>
