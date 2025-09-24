<?php
// Cấu hình kết nối MySQL
$host = 'localhost';
$dbname = 'quanlydiem';
$username = 'root'; // Thay bằng username của bạn
$password = ''; // Thay bằng password của bạn

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>