<?php
// hash_password.php

// Mật khẩu gốc
$password = '1';

// Mã hóa mật khẩu
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// In ra kết quả
echo "<h3>Mật khẩu gốc: $password</h3>";
echo "<h3>Mật khẩu đã mã hóa:</h3>";
echo "<pre>$hashedPassword</pre>";
?>