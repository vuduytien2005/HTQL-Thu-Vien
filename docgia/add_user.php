<?php
include 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Kiểm tra trùng
    $check = $conn->prepare("SELECT * FROM doc_gia WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $message = "Username hoặc email đã tồn tại!";
    } else {
        $stmt = $conn->prepare("INSERT INTO doc_gia (username,password,email,role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $password, $email);
        $stmt->execute();
        $stmt->close();
        $message = "Thêm độc giả thành công!";
    }
    $check->close();
}
?>

<h2>Thêm độc giả</h2>
<form method="POST">
    Username: <input type="text" name="username" placeholder="Username" required><br>
    Email: <input type="email" name="email" placeholder="Email" required><br>
    Password: <input type="password" name="password" placeholder="Password" required><br>
    <input type="submit" value="Thêm">
</form>
<p><?php echo $message; ?></p>
