<?php
include 'db.php';
$Ma_doc_gia = $_GET['Ma_doc_gia'];

$stmt = $conn->prepare("DELETE FROM doc_gia WHERE Ma_doc_gia=? AND role='user'");
$stmt->bind_param("i",$Ma_doc_gia);
$stmt->execute();
$stmt->close();

header("Location: manage_users.php");
exit();
