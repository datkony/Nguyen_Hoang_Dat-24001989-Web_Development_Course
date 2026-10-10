<?php
// Nội dung file config/database.php
$host = 'localhost';
$dbname = 'shopping_cart'; // <-- Tên database bạn vừa tạo ở trên
$username = 'root'; // Mặc định của XAMPP
$password = ''; // XAMPP mặc định không có mật khẩu

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Kết nối thành công!
} catch(PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
}
?>