<?php
include 'connnect.php';
$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM account WHERE id=? AND role='user'");
$stmt->bind_param("i",$id);
$stmt->execute();
$stmt->close();

header("Location: manage_users.php");
exit();
