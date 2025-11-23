<?php
include 'connnect.php';

$search = "";
if(isset($_GET['search'])){
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM account WHERE role='user' AND (username LIKE ? OR email LIKE ?)");
    $like = "%$search%";
    $stmt->bind_param("ss",$like,$like);
} else {
    $stmt = $conn->prepare("SELECT * FROM account WHERE role='user'");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Danh sách độc giả</h2>
<form method="GET">
    Tìm kiếm: <input type="text" name="search" placeholder="Tìm kiếm" value="<?php echo $search; ?>">
    <input type="submit" value="Tìm">
</form>
<a href="add_user.php">Thêm độc giả mới</a>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th><th>Username</th><th>Email</th><th>Ngày tạo</th><th>Hành động</th>
    </tr>
    <?php while($row=$result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['id'];?></td>
        <td><?php echo $row['username'];?></td>
        <td><?php echo $row['email'];?></td>
        <td><?php echo $row['created_at'];?></td>
        <td>
            <a href="edit_user.php?id=<?php echo $row['id'];?>">Sửa</a> |
            <a href="delete_user.php?id=<?php echo $row['id'];?>" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
        </td>
    </tr>
    <?php endwhile;?>
</table>
