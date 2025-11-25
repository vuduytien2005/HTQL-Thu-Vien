<?php
include '../config/db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $ho_ten    = $_POST['ho_ten'];
    $ngay_sinh = $_POST['ngay_sinh'];
    $gioi_tinh = $_POST['gioi_tinh'];
    $dia_chi   = $_POST['dia_chi'];
    $sdt       = $_POST['sdt'];
    $email     = $_POST['email'];

    // Kiểm tra trùng email
    $check = $pdo->prepare("SELECT * FROM doc_gia WHERE Email = :email");
    $check->execute([':email' => $email]);

    if ($check->rowCount() > 0) {
        $message = "❌ Email đã tồn tại!";
    } else {

         // 🔥 LẤY MA_DOC_GIA TIẾP THEO
        $queryID = $pdo->query("SELECT MAX(Ma_doc_gia) AS max_id FROM doc_gia");
        $row = $queryID->fetch();
        $ma_doc_gia = ($row['max_id'] ?? 0) + 1;

        // Thêm độc giả
        $sql = "INSERT INTO doc_gia ( Ma_doc_gia, Ho_ten, Ngay_sinh, Gioi_tinh, Dia_chi, SDT, Email)
                VALUES (:ma_doc_gia, :ho_ten, :ngay_sinh, :gioi_tinh, :dia_chi, :sdt, :email)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':ma_doc_gia' => $ma_doc_gia,
            ':ho_ten'   => $ho_ten,
            ':ngay_sinh'=> $ngay_sinh,
            ':gioi_tinh'=> $gioi_tinh,
            ':dia_chi'  => $dia_chi,
            ':sdt'      => $sdt,
            ':email'    => $email
        ]);

        header("Location: list_user.php? $message = ✅ Thêm độc giả thành công!");
    }
}
?>

<h2>Thêm độc giả</h2>
<form method="POST">
    Họ tên: <input type="text" name="ho_ten" placeholder="Họ tên" required><br>

    Ngày sinh: <input type="date" name="ngay_sinh" placeholder="Ngày sinh" required><br>

    Giới tính:
    <select name="gioi_tinh" placeholder="Giới tính" required>
        <option value="Nam">Nam</option>
        <option value="Nữ">Nữ</option>
    </select><br>

    Địa chỉ: <input type="text" name="dia_chi" placeholder="Địa chỉ" required><br>

    SĐT: <input type="text" name="sdt" placeholder="Số điện thoại" required><br>

    Email: <input type="email" name="email" placeholder="Email" required><br>

    <input type="submit" value="Thêm">
</form>

<p><?php echo $message; ?></p>
